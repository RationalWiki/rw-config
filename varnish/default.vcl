vcl 4.0;

# This is based on the vcl suggested on
# https://www.mediawiki.org/wiki/Manual:Varnish_caching#Configuring_Varnish_4.x

# set default backend if no server cluster specified
backend default {
    .host = "198.74.57.169";
    .port = "80";
}

# vcl_recv is called whenever a request is received
sub vcl_recv {
    # Get X-Forwarded-For from nginx
    if (req.http.X-Forwarded-For) {
        set req.http.X-Forwarded-For =  req.http.X-Forwarded-For;
    } else {
        set req.http.X-Forwarded-For = client.ip;
    }
    set req.backend_hint= default;

    # This uses the ACL action called "purge". Basically if a request to
    # PURGE the cache comes from anywhere other than the purge list, ignore it.
    if (req.method == "PURGE") {
        return (purge);
    }

    # Pass any requests that Varnish does not understand straight to the backend.
    if (req.method != "GET" && req.method != "HEAD" &&
        req.method != "PUT" && req.method != "POST" &&
        req.method != "TRACE" && req.method != "OPTIONS" &&
        req.method != "DELETE") {
            return (pipe);
    } /* Non-RFC2616 or CONNECT which is weird. */

    # Pass anything other than GET and HEAD directly.
    if (req.method != "GET" && req.method != "HEAD") {
        return (pass);
    }      /* We only deal with GET and HEAD by default */

    # Pass requests from logged-in users directly.
    # Only detect cookies with "session" and "Token" in file name, otherwise nothing get cached.
    if (req.http.Authorization || req.http.Cookie ~ "session" || req.http.Cookie ~ "Token") {
        return (pass);
    } /* Not cacheable by default */

    # Force miss if the request is a no-cache request from the client.
    # This is documented to work in Varnish 4.1 but is apparently missing in Varnish 4.0
    #if (req.http.Cache-Control ~ "no-cache") {
    #    req.hash_always_miss = 1;
    #}

    # normalize Accept-Encoding to reduce vary
    if (req.http.Accept-Encoding) {
        if (req.http.User-Agent ~ "MSIE 6") {
            unset req.http.Accept-Encoding;
        } elsif (req.http.Accept-Encoding ~ "gzip") {
            set req.http.Accept-Encoding = "gzip";
        } elsif (req.http.Accept-Encoding ~ "deflate") {
            set req.http.Accept-Encoding = "deflate";
        } else {
            unset req.http.Accept-Encoding;
        }
    }

    return (hash);
}

sub vcl_pipe {
    # Note that only the first request to the backend will have
    # X-Forwarded-For set.  If you use X-Forwarded-For and want to
    # have it set for all requests, make sure to have:
    # set req.http.connection = "close";

    # This is otherwise not necessary if you do not do any request rewriting.

    set req.http.connection = "close";
}

# Called if the cache has a copy of the page.
sub vcl_hit {
    if (!obj.ttl > 0s) {
        return (pass);
    }

    # Force miss if the request is a no-cache request
    # Doesn't work on Varnish 6.2
    #if (req.http.Cache-Control ~ "no-cache") {
    #    return (miss);
    #}
}

# Called if the cache does not have a copy of the page.
sub vcl_miss {
    return (fetch);
}

# Called after a document has been successfully retrieved from the backend.
sub vcl_backend_response {
    # set minimum timeouts to auto-discard stored objects
    set beresp.grace = 120s;

    if (!beresp.ttl > 0s) {
        set beresp.uncacheable = true;
        return (deliver);
    }

    if (beresp.http.Set-Cookie) {
        set beresp.uncacheable = true;
        return (deliver);
    }

    if (beresp.http.Authorization && !beresp.http.Cache-Control ~ "public") {
        set beresp.uncacheable = true;
        return (deliver);
    }

    return (deliver);
}

sub vcl_deliver {
    if (req.url ~ "^/wiki/" || req.url ~ "^/w/index\.php" || req.url ~ "^/\?title=") {
        set resp.http.Cache-Control = "private, s-maxage=0, max-age=0, must-revalidate";
    }
}

# vim: set et sw=4 sts=4
