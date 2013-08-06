
<html>
    <head>
        <title>Architecture of Filebanksta</title>
        <link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/css/bootstrap-combined.min.css" rel="stylesheet">
        <link href="filelib.css" rel="stylesheet">
        <script src="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/js/bootstrap.min.js"></script>
    </head>

    <html>

    <div class="container">

        <div class="row">

            <div class="span12">

                <h1>Architecture of Filebanksta</h1>

                <h2>Filebanksta's mission</h2>

                <p>
                    Filebanksta is a file library component for PHP developers.
                    All kinds of end user applications can and have been built around it, but the library itself
                    doesn't care about that.
                </p>

                <p>
                    The library has been built for a singular purpose, to answer one question:
                    how to handle files <em>uploaded by the end users of an application</em>. The basic needs are practically
                    always the same.
                </p>


                <ul>
                    <li>Store the originals.</li>
                    <li>
                        Convert the original to a more suitable form (scale images, convert raw video to web formats, watermark)
                        and store these versions.
                    </li>
                    <li>Control access to the files in the system.</li>
                    <li>Publish the files somewhere somehow (locally, in a CND, in the cloud) so end users can access them.</li>
                    <li>Save metadata so the uploaded stuff can be associated to objects and used in other meaningful ways.</li>
                    <li>
                        Be able to redo everything when UX guys decide that pixels should be round in images and the thumbnail
                        should be 193 pixels wide instead of 192.
                    </li>
                </ul>

                <p>
                    After I had made like 2-3 different custom file- and mediabankstas, I saw an emerging pattern.
                    A reusable filebanksta could be designed, it's subcomponents made pluggable and abstracted away.
                    It took some years, trial and error with many not-so-custom-anymore filebankstas, but yes,
                    THE Filebanksta was born in the process.
                </p>

                <h2>Core architecture</h2>


                <p>
                    Filebanksta is basically a virtual filesystem consisting of <em>folders</em> and <em>files</em>.
                    No more, no less. There is one <em>root folder</em> which can hold 1-n subfolders. Any folder,
                    including the root folders, can contain 0-n files, and no folder can contain two files
                    with the same filename.
                </p>

                <p>
                    Each file belongs a <em>file profile</em>. Simply put, profile is just metadata that helps
                    differentiate the files in the Filebanksta. Files (and folders) of course have other metadata too,
                    like names, file sizes and mime type.
                </p>

                <p>
                    Invisible to the eye, each file is connected to a <em>resource</em>. Resources are smart:
                    if conditions (more about those later) allow it, multiple files can share a resource. No space
                    is wasted: a thousand files of the same three gigabyte video refer to the same underlying resource
                    and only three gigabytes of space is consumed.
                </p>

                <h2>Plugins</h2>

                <p>
                    Any extra functionality within a Filebanksta instance is achieved via <em>plugins</em>.
                    At the heart of the system there lies an event system, and via hooking to these events
                    Filebanksta plugins can pretty much achieve anything they desire.
                </p>

                <p>
                    The core contains an interface for <em>version provider</em> plugins. File versions are integral
                    to Filebanksta. In it's jargon, versioning doesn't mean the history of the files but the different
                    presentations created from the original file uploaded to the system. Filebanksta never uses
                    the original uploaded file for anything more than safekeeping. All versions can be redone
                    time and again because the original remains unspoiled.
                </p>

                <p>
                    A version could be a thumbnail of an image, a document converted to PDF, a video in a web format.
                    The core zencoder plugin, for example, uses the Zencoder web service to create 1-n web videos to be
                    used in your app. Image processing plugins are of course included too, that being the most
                    common versioning need.
                </p>

                <p>
                    Plugins can do much more. You can hook into access control with them. You can automatically
                    randomize the filenames so the same file can be uploaded. The possibilities are endless, and
                    if a plugin doesn't already exist you can easily make one yourself.
                </p>


                <h2>Metadata backend</h2>

                <p>
                    Filebanksta's metadata (information about files, folders, resources) can be stored in any
                    permanent storage. In Filebanksta's jargon, this is the <em>backend</em>, and a <em>platform</em>
                    backs the backend.
               </p>

                <p>
                    Relational databases (via Doctrine) and MongoDB are currently supported as platforms
                    out of the box. For quick and dirty development / testing purposes a file-based JSON platform
                    is also provided, but that, my dear friend, is not atomic or consistent in any ways!
                </p>

                <p>
                    The backend tries to be nice and only query the platform when necessary. Caching
                    will, when it appears some time before the 1.0 release, make Filebanksta even faster.
                </p>


                <h2>Storage</h2>

                <p>
                    Storage, in Filebanksta jargon, means the physical storage of all the physical files in a
                    Filebanksta instance. A filesystem is a safe and straightforward choice, but GridFS is also
                    supported out of the box.
                </p>

                <p>
                    Via the Gaufrette adapter (todo), Filebanksta integrates to practically any kind of file
                    storage available.
                </p>

                <h2>Authorization & publishing</h2>

                <p>
                    Filebanksta's authorization plugin hooks to all relevant events. Via adapters the authorization
                    component is pluggable to frameworks' and libraries own authorization systems (Symfony 2, Zend Framework 2 are
                    supported out of the box). You can also forget the whole authorization subsystem and implement
                    access control totally within your own app's context.
                </p>

                <p>
                    Filebanksta's opinion is that all world readable files should be presented to the end user
                    outside Filebanksta's scope. This is achieved via publishing them: all world-viewable
                    files are <em>published</em> somewhere (locally inside the webscope, in the cloud) and
                    Filebanksta providers URLs to them.
                </p>

                <p>
                    A configurable and switchable <em>linker</em> creates all URLs. Want sequential, functional urls? No problem.
                    Fancy folder-based pretty urls? You got them.
                </p>

                <p>
                    If your files are stored and published within the same filesystem (or in the dev env),
                    Filebanksta's publisher can be made to use symbolic links (relative or absolute)
                    to minimize space usage. It's really neat!
                </p>

                <p>
                    But what if you really need both access control and efficient delivery of files? No worries!
                    Filebanksta's <em>renderer</em> component hooks up to external libraries' HTTP request/response
                    scope and supports <strong>accelerated rendering</strong> with Nginx or any other capable
                    HTTP server (Apache with x-sendfile, etc.).
                </p>

                <h2>Asynchronous operations</h2>

                <p>
                    Some things can not be done while the user waits. Post-processing a thousand fat images
                    uploaded images or a gigabyte of video to 5 different formats might be some examples.
                    Filebanksta supports asynchronous operations for pretty much everything that can be done
                    asynchronously.
                </p>

                <p>
                    Just hook up your Filebanksta app to the nearest message queue (RabbitMQ supported out of the box)
                    and you're good to go.
                </p>
            </div>

        </div>


    </div>


    </html>


</html>


<?php
