function Download (source) {
    this.version = source[0];
    this.regex = source[1];
    this.url = source[2];
    this.desc = source[3];

    // Strip '.0' version suffix, unless it is for a minor version.
    // It is always incorrect for Mercurial releases.
    var points = this.version.split('.');
    if (points.length > 2 && points[points.length-1] == "0") {
        points.pop();
        this.version = points.join(".");
    }
}

Download.prototype = {
    matches: function (ua) {
        if (ua.match(this.regex))
            return true;
        return false;
    },

    download: function () {
        document.location.href = this.url;
        return false;
    },

    attr: function (key) {
        return this[key];
    },

    write: function (key) {
        document.write(this[key]);
    }
}


var Downloader = {
    // maximum number of versions to display (0 to display all available)
    maxversions: 2,

    downloads: [],

    init: function (sources) {
        for (i in sources) {
            var source = new Download(sources[i]);
            this.downloads.push(source);
        }
    },

    select: function () {
        var ua = navigator.userAgent;
        for (i in this.downloads) {
            if (this.downloads[i].matches(ua)) {
                return this.downloads[i];
            }
        }
        return null;
    },

    versions: function () {
        var uniq = new Object();
        for (i in this.downloads) {
            uniq[this.downloads[i].version] = 1;
        }
        var versions = new Array();
        for (key in uniq) {
            versions.push(key);
        }
        versions.sort(function (a, b) {
            a = a.toLowerCase();
            b = b.toLowerCase();
            return (a < b) - (b < a);
        });
        return versions;
    },

    listall: function (selector) {
        if (selector == null)
            selector = function (o) { return true; }

        // copy the download list, selecting only wanted nodes
        var downloads = new Array();
        for (i in this.downloads) {
            if (selector(this.downloads[i])) {
                downloads.push(this.downloads[i]);
            }
        }

        // alpha-sort it by description (case-folded)
        downloads.sort(function (a, b) {
            a = a.desc.toLowerCase();
            b = b.desc.toLowerCase();
            return (b < a) - (a < b);
        });

        var desc;
        var out = ''
        for (i in downloads) {
            var dl = downloads[i];
            var ua = navigator.userAgent;
            if (dl.matches(ua))
                desc = '<em>' + dl.desc + '</em>';
            else
                desc = dl.desc;
            out += '<tr>\n<td>' + desc + '</td>' +
                   '<td></td>' +
                   '<td><a href="' + dl.url + '">download</a></td>' +
                   '</tr>';
        }
        return out;
    },

    table: function (name, selector) {
        var out = '';
        out += '<table border="0" cellspacing="0" ' +
               'cellpadding="0" class="latest" width="100%">\n';
        out += '<thead>\n';
        out += '<tr>\n';
        out += '<th>Mercurial ';
        out += name;
        out += '</th>';
        out += '<th></th>';
        out += '<th></th>';
        out += '</tr>';
        out += '</thead>';
        out += '<tbody>';
        out += this.listall(selector);
        out += '</tbody>';
        out += '</table>';
        out += '<br/>';
        return out;
    }
};
