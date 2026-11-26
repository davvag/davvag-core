"""Lint PHP sources and scan removed/deprecated function calls using PHP tokens."""
import argparse
import concurrent.futures
import json
import os
from pathlib import Path
import shutil
import subprocess
import sys

ROOT = Path(__file__).resolve().parent.parent
SKIP = {'.git', '.tmp', 'tmp', '.venv', 'node_modules', '__pycache__'}


def php_files(directory):
    # Do not follow arbitrary links outside the checkout. The linked tenant is
    # included separately only when requested explicitly.
    for current, dirs, files in os.walk(directory, followlinks=False):
        dirs[:] = [d for d in dirs if d not in SKIP and not (Path(current) / d).is_symlink()]
        yield from (Path(current) / f for f in files if f.lower().endswith('.php'))


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--php', default=shutil.which('php') or r'C:\xampp\php\php.exe')
    parser.add_argument('--include-tenant', action='store_true')
    parser.add_argument('--smoke', action='store_true', help='Also run the offline compatibility regressions')
    parser.add_argument('--json', type=Path, help='Optional JSON report destination')
    args = parser.parse_args()
    env = dict(os.environ, XDEBUG_MODE='off')
    version = subprocess.run([args.php, '-r', 'echo PHP_VERSION;'], capture_output=True, text=True, check=True, env=env).stdout
    if tuple(int(part) for part in version.split('.')[:2]) < (8, 5):
        parser.error('Run the audit with PHP 8.5 or newer to detect target-version deprecations.')
    files = list(php_files(ROOT))
    tenant = ROOT / 'davvag-core/localhost'
    if args.include_tenant and tenant.is_dir():
        files.extend(php_files(tenant))
    files = sorted(set(files))

    def lint(path):
        result = subprocess.run([args.php, '-d', 'log_errors=0', '-d', 'display_errors=stderr',
                                 '-d', 'error_reporting=32767', '-l', str(path)],
                                capture_output=True, text=True, env=env)
        messages = [line for line in (result.stdout + result.stderr).splitlines()
                    if line.strip() and not line.startswith('No syntax errors')]
        return {'file': str(path.relative_to(ROOT)), 'exit': result.returncode, 'messages': messages}

    with concurrent.futures.ThreadPoolExecutor(max_workers=8) as pool:
        results = list(pool.map(lint, files))
    issues = [r for r in results if r['exit'] or r['messages']]
    scan = subprocess.run([args.php, '-d', 'log_errors=0', str(ROOT / 'tests/php85-function-audit.php')],
                          input=json.dumps([str(p) for p in files]), text=True, capture_output=True, env=env)
    try:
        functions = json.loads(scan.stdout)
    except json.JSONDecodeError:
        print(scan.stdout + scan.stderr, file=sys.stderr)
        return 1
    report = {'php': version, 'files': len(files), 'lint_issues': issues, **functions}
    if args.json:
        args.json.write_text(json.dumps(report, indent=2), encoding='utf-8')
    print(f"PHP {version}: checked {len(files)} files; {len(issues)} lint issues; "
          f"{len(functions['findings'])} unsupported/deprecated function calls; "
          f"{len(functions['guarded'])} guarded legacy calls.")
    if issues or functions['findings']:
        print(json.dumps({'lint_issues': issues, 'functions': functions['findings']}, indent=2))
        return 1
    if args.smoke:
        plugin_roots = [ROOT / 'plugins']
        if args.include_tenant and tenant.is_dir():
            plugin_roots.append(tenant / 'plugins')
        for plugins in plugin_roots:
            subprocess.run([args.php, '-d', 'log_errors=0', str(ROOT / 'tests/php85-compatibility.php'), str(plugins)], env=env, check=True)
    return 0


if __name__ == '__main__':
    sys.exit(main())
