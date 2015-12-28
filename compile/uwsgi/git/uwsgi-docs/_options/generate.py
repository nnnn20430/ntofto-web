from optutil import Config
import os
import sys
import json

PRELUDE = """
.. *********************************************************************
.. This page has been automatically generated by `_options/generate.py`!
.. *********************************************************************

%(title)s Options
------------------------------------------------------------------------

""".strip()


FULL_PRELUDE = """
.. *********************************************************************
.. This page has been automatically generated by `_options/generate.py`!
.. *********************************************************************

Configuration Options
=====================

uWSGI and the various plugins it consists of is almost infinitely configurable.

There's an exhaustive and exhausting list of all options below. Take a deep breath and don't panic -- the list below is long, but you don't need to know everything to start using uWSGI.

"""


def render_rst(config, prelude):
    output = [prelude % vars(config)]
    write = output.append

    for i, section in enumerate(config.sections):
        write("")
        refname = section.refname
        if not refname and i == 0:
            refname = "Options%s" % config.filename_part

        if refname:
            write(".. _%s:" % refname)
            write("")

        write("%s" % section.name)
        write("^" * len(section.name))
        write("")
        if section.docs:
            write(u".. seealso::")
            write(u"")
            for doc in section.docs:
                write(u"   :doc:`%s`" % doc)
            write("")
        
        for opt in section.options:
            write(".. _Option%s:" % opt.cc_name)
            write("")
            header = (", ".join("``%s``" % name for name in opt.names))
            write(header)
            write("~" * len(header))
            write("**Argument:** %s" % opt.get_argument())
            if opt.default:
                write("**Default:** %s" % opt.default)
            write("")

            write(opt.get_description())
            if opt.help:
                write("")
                write(opt.help)
            if opt.docs:
                write("")
                write(".. seealso:: %s" % ", ".join(u":doc:`%s`" % topic for topic in opt.docs))
            write("")

    return output

def write_output(output, filename):
    target_file = os.path.realpath(os.path.join(os.path.dirname(__file__), "..", filename))
    with file(target_file, "wb") as out_file:
        out_file.write("\n".join(output).encode("UTF-8").lstrip())

def read_configs():
    import optdefs
    funcs = [(c, f) for (c, f) in [(c, getattr(optdefs, c)) for c in dir(optdefs) if c.endswith("_options")] if callable(f)]
    funcs.sort(key = lambda (c, f): ((-9000, None) if c.startswith("core_") else (0, c))) # Shunt core options up top, use alpha otherwise

    filenames = []
    configs = []
    for funcname, func in funcs:
        print >>sys.stderr, "Calling %r..." % funcname
        config = func()
        filename = "Options%s.rst" % config.filename_part
        configs.append((filename, config))

    return configs

def write_rst():

    rst_lines = []
    for filename, config in read_configs():
        rst_lines.extend(render_rst(config, ""))

    print "Writing Options.rst..."
    write_output([FULL_PRELUDE] + rst_lines, "Options.rst")

def find_documented_options():
    options = set()
    for filename, config in read_configs():
        for section in config.sections:
            for opt in section.options:
                options |= set(opt.names)
    print json.dumps(sorted(options))

def main():
    import argparse
    ap = argparse.ArgumentParser()
    ap.add_argument("--action", default="generate", help="action (generate, list)")
    args = ap.parse_args()
    if args.action == "generate":
        return write_rst()
    if args.action == "list":
        return find_documented_options()

if __name__ == '__main__':
    main()