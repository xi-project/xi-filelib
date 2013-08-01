
<html>
    <head>
        <title>Welcome to Xi Filelib</title>
        <link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/css/bootstrap-combined.min.css" rel="stylesheet">
        <link href="filelib.css" rel="stylesheet">
        <script src="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/js/bootstrap.min.js"></script>
    </head>

    <html>

    <div class="container">

        <div class="row">

            <div class="span12">

                <h1>Welcome to Filelib</h1>

                <h2>Basic examples</h2>

                <dl>
                    <dt><a href="hello-world.php">Hello World</a></dt>
                    <dd>Simplest of simple use cases. Upload a picture and render it to screen.</dd>

                    <dt><a href="publisher.php">Publisher</a></dt>
                    <dd>
                        Your files are exposed to the world when they are published. Original files stored in
                        Filelib are just stored for safekeeping. Only <em>versions</em> of files can be published,
                        and a publishable carbon copy of the original can be achieved with a <em>plugin</em>.
                    </dd>

                    <dt><a href="versions.php">Versions</a></dt>
                    <dd>
                        Plugins can also create modified versions of your files. The image version plugin, for example,
                        can do anything with your images. Scale them, watermark them, so forth.
                    </dd>

                    <dt><a href="beautifurls.php">Beautifurls</a></dt>
                    <dd>The URLs for files that are published can be easily configured.</dd>

                    <dt><a href="access-control.php?writable=1&publishable=1">Access control</a></dt>
                    <dd>
                        Access control can be achieved via the authorization plugin. Filelib's authorization
                        supports any framework via authorization adapters, and the renderer component is
                        authorization aware.
                    </dd>

                    <dt><a href="renderer.php">Renderer</a></dt>
                    <dd>
                        Renderer easily hooks Filelib to frameworks' and libraries' HTTP request / response cycle.
                        Renderer also supports <em>acceleration</em>, ie. using Filelib's authorization
                        but serving the actual file via a acceleration-enabled HTTP server like Nginx.
                    </dd>

                    <h2>Advanced examples</h2>

                    <p>
                        Copy <code>../constants.example.php</code> to <code>../constants.php</code>
                        and configure.
                    </p>

                    <dt><a href="async.php">Asynchronous processing</a></dt>
                    <dd>
                        Some operations, like creating a version of a 2 gigabyte video or batch creating 10.000
                        images at the same time, are expensive and should not keep the user waiting longer than
                        necessary. Filelib supports asynchronous operation via message queues.
                        <span class="label label-info">RabbitMQ</span>
                    </dd>

                    <dt><a href="zencoder.php">Videos</a></dt>

                    <dd>
                        Web video can easily be created with the Zencoder plugin.
                        <span class="label label-info">RabbitMQ</span> <span class="label label-info">Zencoder</span>
                    </dd>

                </dl>

            </div>

        </div>


    </div>


    </html>


</html>


<?php
