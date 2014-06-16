
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

                <p>
                    Filelib, <em>Filebanksta</em> among friends, is a file library component for PHP.
                </p>

                <p>
                    Filebanksta is purely a developer's tool. All kinds of end user applications can and have
                    been built around it, but the library itself doesn't care. Filelib has been engineered for
                    a singular purpose, to answer one question: what to do with and how to handle files
                    <em>uploaded byt the app's end users</em>.
                </p>

                <dl>
                    <dt><a href="history.php">History of Filebanksta</a></dt>
                    <dd>
                        Filebanksta goes back to 2004, so this stuff's been brewing for a while. How did we get here,
                        and what was the motivation?
                    </dd>

                    <dt><a href="architecture.php">Architecture of Filebanksta</a></dt>
                    <dd>
                        Filelib's subcomponents are loosely coupled so you can mix and match to your heart's content
                        and app's requirements. What are these subcomponents and what can be achieved with them?
                    </dd>
                </dl>

                <p>
                    Filebanksta is a developer's tool so let's code already. Clickety click the links and look at the code, dude!
                </p>

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
                </dl>

                <h2>Advanced examples</h2>

                <p>
                    Copy <code>../constants.example.php</code> to <code>../constants.php</code>
                    and configure.
                </p>

                <dl>
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

                    <dt><a href="lazy-publisher.php">Lazy publisher</a></dt>
                    <dd>
                        <p>
                            When deciding to operate in lazy mode with version provider plugins that support this
                            (image version plugins, for example), nothing needs to be processed in front. Versions
                            are created on demand. You can clean up space by deleting old, rarely accessed files
                            from the file system and if by chance someone needs them once more they are recreated.
                        </p>

                        <p>
                            When working with publishers and lazy mode you must of course hook your HTTP server
                            to forward all non-found filelib files to the backend who can then use reversible
                            linkers to create and render the version.
                        </p>
                    </dd>
                </dl>

            </div>

        </div>


    </div>


    </html>


</html>


<?php
