#
# (c) 2014 David Soria Parra <dsp@php.net>
#
# This software may be used and distributed according to the terms of the
# GNU General Public License version 2 or any later version.
import os
import flask

app = flask.Flask(__name__, static_url_path='')


@app.route('/')
def indexpage():
    return flask.render_template('frontpage.html')


@app.route('/<site>')
def about(site=None):
    if not site:
        flask.abort(404)
    root = os.path.dirname(os.path.abspath(__file__))
    tpath = os.path.join(root, 'templates', site, 'index.html')
    if os.path.exists(tpath):
        t = os.path.join(site, 'index.html')
        return flask.render_template(t)
    spath = os.path.join(root, 'static', site)
    if os.path.exists(spath):
        return app.send_static_file(site)
    flask.abort(404)


if os.getenv("HGWEBSITE_DEBUG", None):
    app.debug = True

if __name__ == '__main__':
    app.run()
