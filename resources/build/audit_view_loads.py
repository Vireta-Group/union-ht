"""Complete audit: every view load in every controller, and whether the file exists.

Covers both $this->load->view('x') and $this->template->back('x') / ::sonod('x'),
which the earlier pass did not — it only looked at the template-> ones, so a
missing $this->load->view target would have been missed.

Prints, per controller: every view name it loads, the file that name resolves to
if any, and a verdict. Commented-out lines are excluded.
"""
import io
import os
import re
import sys

root = 'application/views'
files = set()
for dirpath, _dirs, names in os.walk(root):
    for n in names:
        if n.endswith('.php'):
            files.add(os.path.relpath(os.path.join(dirpath, n), root)[:-4])

# view name (with or without a subdir) -> does exactly that path exist?
def resolves(name):
    if name in files:
        return name + '.php'
    base = name.split('/')[-1]
    for f in files:
        if f.split('/')[-1] == base:
            return '(ambiguous: %s)' % f
    return None

# a view may also be under views/<dir>/<name> when the caller passed only <name>
by_base = {}
for f in files:
    by_base.setdefault(f.split('/')[-1], []).append(f)

total = ok = missing = 0
report = []

for fn in sorted(os.listdir('application/controllers')):
    if not fn.endswith('.php'):
        continue
    path = 'application/controllers/' + fn
    src = io.open(path, encoding='utf-8', errors='replace').read()
    found = []
    for i, line in enumerate(src.split('\n')):
        if line.lstrip().startswith(('//', '*', '/*', '#')):
            continue
        for m in re.finditer(
                r"(?:load->view|template->(?:back|sonod|front|saas))\(\s*'([^']+)'", line):
            name = m.group(1)
            if not re.fullmatch(r"[A-Za-z0-9_/]+", name):
                continue
            found.append((i + 1, name))
    if not found:
        continue
    for lineno, name in found:
        total += 1
        hit = resolves(name)
        if hit is None:
            base = name.split('/')[-1]
            cands = by_base.get(base, [])
            if cands:
                report.append((fn, lineno, name, 'MISSING PREFIX? has ' + ', '.join(cands)))
                missing += 1
            else:
                report.append((fn, lineno, name, 'NO SUCH FILE'))
                missing += 1
        elif hit.startswith('(ambiguous'):
            report.append((fn, lineno, name, hit))
            ok += 1
        else:
            ok += 1

print('view loads: %d total, %d resolve, %d do not\n' % (total, ok, missing))
print('%-16s %-6s %-38s %s' % ('CONTROLLER', 'LINE', 'VIEW NAME', 'VERDICT'))
print('-' * 96)
for fn, lineno, name, verdict in report:
    flag = '' if verdict.startswith('(ambiguous') else '  <-- BROKEN'
    print('%-16s %-6s %-38s %s%s' % (fn, lineno, name, verdict, flag))
sys.exit(1 if missing else 0)
