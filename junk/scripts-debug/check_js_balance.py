import sys

path = sys.argv[1] if len(sys.argv)>1 else 'production_served_v.js'
text = open(path, 'rb').read()
try:
    s = text.decode('utf-8')
except Exception:
    s = text.decode('latin-1')

in_single = in_double = in_backtick = in_block = False
escape = False
line = 1
col = 0
first_unquoted_lt = None
for i,ch in enumerate(s):
    col += 1
    if ch == '\n':
        line += 1
        col = 0
        if in_single:
            in_single = False
        if in_double:
            in_double = False
        escape = False
        continue
    if in_block:
        if ch == '*' and i+1 < len(s) and s[i+1] == '/':
            in_block = False
            # skip next
            continue
        else:
            continue
    if not (in_single or in_double or in_backtick):
        # check for line comment
        if ch == '/' and i+1 < len(s) and s[i+1] == '/':
            # skip until newline
            continue
        if ch == '/' and i+1 < len(s) and s[i+1] == '*':
            in_block = True
            continue
    if ch == "'" and not (in_double or in_backtick):
        if not in_single:
            in_single = True
            escape = False
            continue
        else:
            if not escape:
                in_single = False
            else:
                escape = False
            continue
    if ch == '"' and not (in_single or in_backtick):
        if not in_double:
            in_double = True
            escape = False
            continue
        else:
            if not escape:
                in_double = False
            else:
                escape = False
            continue
    if ch == '`' and not (in_single or in_double):
        if not in_backtick:
            in_backtick = True
            escape = False
            continue
        else:
            if not escape:
                in_backtick = False
            else:
                escape = False
            continue
    if ch == '\\' and (in_single or in_double or in_backtick):
        escape = not escape
        continue
    else:
        escape = False
    # detect '<' outside quotes/comments
    if ch == '<' and not (in_single or in_double or in_backtick or in_block):
        if first_unquoted_lt is None:
            first_unquoted_lt = (line, col, s[max(0,i-40):min(len(s),i+40)])

print('File:', path)
print('Unclosed states: single={}, double={}, backtick={}, block={}'.format(in_single, in_double, in_backtick, in_block))
if first_unquoted_lt:
    ln,co,ctx = first_unquoted_lt
    print('First unquoted < at line', ln, 'col', co)
    print('Context (40 chars around):')
    print(repr(ctx))
else:
    print('No unquoted < found (all < inside strings/comments).')
