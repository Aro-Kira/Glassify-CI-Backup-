import sys
path = sys.argv[1] if len(sys.argv)>1 else 'production_served_v.js'
with open(path, 'rb') as f:
    b = f.read()
try:
    s = b.decode('utf-8')
except Exception:
    s = b.decode('latin-1')
line = s.splitlines()[82]
print('LINE83 REPR:')
print(repr(line))
print('\nLINE83 RAW:')
print(line)
