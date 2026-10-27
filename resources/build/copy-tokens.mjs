/**
 * Copy the design tokens to where the browser can fetch them.
 *
 * portal.css @imports tokens.css, so the Tailwind build inlines the tokens
 * and the public pages need no extra <link>. The admin does not go through
 * Tailwind at all — p/h.php links tokens.css and brand.css directly — so the
 * file has to exist under inc/assets/css/ as well.
 *
 * This is a copy rather than a symlink on purpose: inc/ is what gets
 * deployed, and a symlink pointing back into resources/ would break the
 * moment only inc/ is shipped.
 *
 * The copy is content-checked, not just copied: if tokens.css changes without
 * the served copy being rebuilt, the admin would silently render the old
 * palette while the public pages render the new one. `npm run build` warns
 * when they have drifted.
 */
import { readFileSync, writeFileSync, mkdirSync, existsSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const here = dirname(fileURLToPath(import.meta.url));
const root = join(here, '..', '..');

const SRC = join(root, 'resources', 'css', 'tokens.css');
const DEST = join(root, 'inc', 'assets', 'css', 'tokens.css');
const BANNER = '/* GENERATED from resources/css/tokens.css by npm run build:admin. Do not edit. */\n';

const source = BANNER + readFileSync(SRC, 'utf8');

mkdirSync(dirname(DEST), { recursive: true });

const previous = existsSync(DEST) ? readFileSync(DEST, 'utf8') : null;

if (previous === source) {
  console.log('tokens.css unchanged — nothing to copy');
} else {
  if (previous !== null) {
    console.log('tokens.css CHANGED — served copy updated (admin palette follows)');
  }
  writeFileSync(DEST, source);
  console.log(`tokens.css -> inc/assets/css/tokens.css (${source.length} bytes)`);
}
