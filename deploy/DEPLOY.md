# Deploying to a VPS

The app is PHP 7-era CodeIgniter 3 on PostgreSQL. It was developed behind
Apache; `deploy/nginx/` replaces that. nginx does not read `.htaccess`, so
every deny rule in the repo's `.htaccess` and `uploads/.htaccess` is restated
there — they are not tidying, they are the only thing keeping `.env` and
executable uploads off the internet.

Read this whole file before the first deploy. There is no staging environment
and no rollback script; the manual backups in `/home/darkness/vireta-private/`
are the only way back.

---

## 1. What has to be true before anything works

| Requirement | Why |
|---|---|
| PostgreSQL reachable from PHP | The only driver in use is `pdo` with a `pgsql:` DSN |
| `pdo_pgsql`, `mbstring`, `gd` | Postgres, Bangla text, image resize. mPDF also wants `gd` |
| PHP 8.x | It runs on 8.5 locally. CI3 is 7-era code, so expect deprecation noise |
| Wildcard DNS `*.<apex>` → the VPS | Signup hands out `<slug>.<apex>`; nothing to add per tenant if the wildcard resolves |
| Wildcard TLS cert for `<apex>` | A cert for the apex alone does not cover `dhum.<apex>`, so every tenant gets a browser warning |
| `.env` with `APP_ENV=production`, `APP_ENCRYPTION_KEY`, `APP_TENANT_APEX` | `index.php` returns 503 without them, on purpose |

`vendor/` is not reinstallable — there is no `composer.json` and `config.php`
loads `vendor/autoload.php`. Copy the directory across. Do not run
`composer install` on the server; it will fail and may delete working code.
`Download.php` (mPDF) and `Pdf.php` both need it.

---

## 2. Install

```bash
# nginx + PHP 8.3 FPM
apt update
apt install -y nginx php8.3-fpm php8.3-pgsql php8.3-mbstring php8.3-gd \
               php8.3-curl php8.3-xml php8.3-zip postgresql-client

# copy the app
useradd -r -m -d /var/www/htxvireta htxvireta
rsync -a --exclude '.env' /path/to/HT\ X\ ViretaDev/ /var/www/htxvireta/

# .env — never in version control
install -m 600 /path/to/deploy/.env.example /var/www/htxvireta/.env
$EDITOR /var/www/htxvireta/.env          # key, apex, DB password

# session dir, if you set APP_SESSION_PATH
install -d -o htxvireta -g htxvireta -m 700 /var/lib/htxvireta/sessions

chown -R htxvireta:htxvireta /var/www/htxvireta
# uploads must be writable by the web user, nothing else needs to be
chown -R htxvireta:htxvireta /var/www/htxvireta/uploads
```

Never make the whole tree writable. The only directory the app writes to is
`uploads/`.

---

## 3. Database

```bash
sudo -u postgres psql -c "CREATE USER vireta WITH PASSWORD '...';"
sudo -u postgres psql -c "CREATE DATABASE vireta OWNER vireta;"
pg_dump -h OLD -U OLD -Fc -d OLD -f /tmp/vireta.dump
pg_restore -h 127.0.0.1 -U vireta -d vireta --no-owner /tmp/vireta.dump
```

Then fix the tenant sequence. A restored dump leaves
`ins_titute_ins_titute_p_idds_seq` behind the highest id, and the first signup
after a restore will collide with an existing tenant:

```sql
SELECT setval('ins_titute_ins_titute_p_idds_seq',
              (SELECT max(ins_titute_p_idds) FROM ins_titute));
```

This is not theoretical — it is what happened during testing, and before the
guard in `create_tenant()` was added it silently attached a new parishad's
hostnames and admin account to someone else's tenant.

---

## 4. nginx

```bash
# replace PLACEHOLDER_* first
sed -i 's/PLACEHOLDER_PLATFORM_DOMAIN/vireta.dev/g'        /etc/nginx/sites-available/default
sed -i 's/PLACEHOLDER_TENANT_APEX/viretaup.com/g'          /etc/nginx/sites-available/default
cp deploy/nginx/vireta-app.conf      /etc/nginx/snippets/
cp deploy/nginx/vireta-upstream.conf /etc/nginx/conf.d/
nginx -t && systemctl reload nginx
```

`nginx -t` before every reload. A bad config takes the whole platform down and
there is no health check.

### TLS order matters

Certbot's HTTP-01 challenge needs the vhost answering on port 80, and the
config above redirects everything on 80 to HTTPS. So:

1. Install the config as written (the ACME `location ^~` block is exempt).
2. `certbot certonly --webroot -w /var/www/html -d vireta.dev -d www.vireta.dev`
3. `certbot certonly --webroot -w /var/www/html -d viretaup.com -d '*.viretaup.com'`

The wildcard needs DNS-01, which needs a certbot DNS plugin and API access to
your DNS provider. `--webroot` cannot issue it. Without a wildcard cert every
tenant subdomain shows a browser warning, and the fix is a real certificate,
not an HTTP redirect.

---

## 5. Check it actually works

Every host returns HTTP 200, so status codes prove nothing. Use a value that
exists only in the database:

```bash
# product site: the platform host serves the landing page
curl -s https://vireta.dev/ | grep -c 'id="supportModal"'      # 1
curl -s https://dhum.viretaup.com/ | grep -c 'id="supportModal"' # 0

# a real tenant: pick a member name that only exists in that parishad
curl -s https://dhum.viretaup.com/ | grep -c "জাফর আহম্মেদ"

# the things nginx is responsible for
curl -s -o /dev/null -w '%{http_code}\n' https://vireta.dev/.env          # 404
curl -s -o /dev/null -w '%{http_code}\n' https://vireta.dev/backup.sql    # 404
curl -s -o /dev/null -w '%{http_code}\n' \
     https://dhum.viretaup.com/uploads/x.php                              # 403
```

A **200 with an empty body** from `/dashboard` or `/userManagement` is a
redirect, not a crash — the admin gate emits `Refresh:0;url=…`, and `curl`
does not follow it:

```bash
curl -s -D - -o /dev/null https://dhum.viretaup.com/dashboard | grep -i '^Refresh:'
```

**Test a data route, not `/`.** `/` is guarded by `render_site()`, which sends
unknown hosts to the product site. Every other controller reads `tenant_code()`
directly, so an unregistered host still resolves to a real tenant via the
step-6 primary fallback. A green home-page check proves `render_site()` and
nothing else.

---

## 6. What changed for production, and what to watch

**Signup now creates a subdomain that resolves.** `create_tenant()` writes
three `tenant_domains` rows: the bare slug (what `match_subdomain()` looks up),
the full hostname, and the custom domain if one was given. Exactly one is
`is_primary`. Before, the apex row and the custom-domain row could both be
`TRUE`, and `primary_tenant()` is `ORDER BY d.id LIMIT 1`, so the fallback
tenant for every unknown host depended on insert order.

**`root_domains` is derived from `APP_TENANT_APEX`.** It used to be an empty
array in two places, which meant subdomain matching could never fire at all —
a registered subdomain resolved in DNS and then 404'd in the app. If a new
tenant's subdomain 404s, check for the bare-slug row first:

```sql
SELECT hostname, ins_titute_p_id FROM tenant_domains WHERE hostname = 'theslug';
```

**Passwords are bcrypt, cost 12, and nothing is stored in the clear.** The
hardcoded `['password', 'admin', '841998']` backdoor and the plaintext
`password_viewss_s` comparison are gone, and the column is no longer written.
Five existing accounts still have a legacy `password_viewss_s` value in the
database; nothing reads it any more. Clear it when convenient:

```sql
UPDATE users SET password_viewss_s = NULL WHERE password_viewss_s IS NOT NULL;
```

**Signup is throttled** to 5 per IP per hour, per `APP_SIGNUP_*`. It is off
in development so iterating on the form locally is not interrupted.

**Production refuses to boot** on a published `encryption_key` or a missing
apex, and `/t_diag` is 404 because `is_development` is false.

---

## 7. If you add a deny rule

Add it to the nginx snippet **and** the `.htaccess`, or the dev environment
and production will disagree about what is servable. When you do, put it
*above* the `location ~ \.php$` block — nginx takes the first matching regex
location in file order, so a deny placed after the PHP handler never fires.
