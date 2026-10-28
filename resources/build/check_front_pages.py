"""Structural check on the standalone public pages in application/views/front/.

Run with no arguments; exits non-zero if anything fails.

  python3 resources/build/_check_legacy_front.py

Asserts, per file:
  - exactly one footer partial load
  - no </body> or </html> of its own (front/partials/footer.php closes the
    document, so a second pair means the document is closed twice)
  - the last <script> comes BEFORE the footer load
  - <main> survived
  - header + navbar partials both loaded
  - no legacy footer / navbar classes left in the MARKUP

Comments are stripped before the legacy-class scan. The converted files
document in their docblock exactly which classes they removed
(`footer-section / footer-cta / single-cta / ...`), so a naive scan reports
every one of them as still present — which is how the first version of this
check gave 0/4 clean on files that were already correct.
"""
import io
import re
import sys

FILES = ['search_holding_number_view_pages', 'single_member_views',
         'single_employee_views', 'single_holding_person_view_file', 'add']

LEGACY = ['legacy-profile.css', 'footer-section', 'footer-cta', 'single-cta',
          'cta-text', 'copyright-area', 'my-navbar', 'navbar-brand',
          'glos_text_word', 'nav-active', 'nav-item', 'nav-link']


def strip_php_comments(s):
    s = re.sub(r'/\*.*?\*/', ' ', s, flags=re.S)
    s = re.sub(r'^\s*//.*$', ' ', s, flags=re.M)
    # <?php ... ?> blocks that are pure commentary
    s = re.sub(r'<\?php\s*(?:/\*.*?\*/|//[^\n]*\n|\s)*\?>', ' ', s, flags=re.S)
    return s


bad = 0
for name in FILES:
    p = 'application/views/front/%s.php' % name
    s = io.open(p, encoding='utf-8').read()
    markup = strip_php_comments(s)

    foot = [m.start() for m in re.finditer(r"load->view\('front/partials/footer'", s)]
    scripts = [m.start() for m in re.finditer(r'<script', s)]
    problems = []

    if len(foot) != 1:
        problems.append('footer loads = %d (want 1)' % len(foot))
    for tag in ('</body>', '</html>'):
        if tag in markup:
            problems.append('own %s (footer.php already closes it)' % tag)
    if '<main' not in markup or '</main>' not in markup:
        problems.append('main lost')
    for part in ('partials/header', 'partials/navbar'):
        if part not in s:
            problems.append('missing %s' % part)
    if foot and scripts and max(scripts) > foot[0]:
        problems.append('a <script> sits after the footer load')
    if foot and foot[0] < len(s) * 0.5:
        problems.append('footer load is not near the end (line %d of %d)'
                        % (s[:foot[0]].count('\n') + 1, s.count('\n') + 1))
    for legacy in LEGACY:
        if legacy in markup:
            problems.append('legacy: %s' % legacy)

    print('  %s %-40s %s' % ('OK  ' if not problems else 'FAIL', name + '.php',
                             ', '.join(problems)))
    if problems:
        bad += 1

print('\n  %d/%d clean' % (len(FILES) - bad, len(FILES)))
sys.exit(1 if bad else 0)
