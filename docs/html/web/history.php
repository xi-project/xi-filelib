
<html>
    <head>
        <title>History of Filebanksta</title>
        <link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/css/bootstrap-combined.min.css" rel="stylesheet">
        <link href="filelib.css" rel="stylesheet">
        <script src="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/js/bootstrap.min.js"></script>
    </head>

    <html>

    <div class="container">

        <div class="row">

            <div class="span12">

                <h1>History of Filebanksta</h1>

                <h2>Early years</h2>

                <p>
                    The story of Filebanksta begins in late 2004. My first real job was Webotek, later eMedia Company
                    Finland. They developed a custom CMS where the company's customer stored quite a bit of files.
                </p>

                <p>
                    The access control was folder based - ie. if a user had permission to write to a folder he/she
                    could upload files, if he had read access he could read all the files. All seemed fine and legit,
                    and as some of the files seemed to have semi-pretty URLs I couldn't wait to see what black magicks
                    took care of the access control read-wise.
                </p>

                <p>
                    Well, it turned out that there was no ACL read-wise. All the files were dumped inside the
                    webscope and if one guessed the url even the files in secured virtual folders could be downloaded.
                    The guy who had replaced download urls like <code>iisi3.pl?sid=download&id=xxx</code> type of code with
                    straightforward semi-pretty urls had removed even the illusion of safety because the file traffic
                    didn't reach the Perl/Kylix side of the software at all.
                </p>

                <p>
                    Anyways, the basic structure was OK. Virtual <em>folders</em> and <em>files</em> in a structure
                    familiar to all desktop computing. And the semi-pretty URLs were, well, semi-pretty, so I
                    at least liked them better than the horrible <code>iisi3.pl?sid=vomitarium&id=terrible&download=true</code>
                    fugly URLs.
                </p>

                <p>
                    In the spring of 2005 I rewrote the whole CMS in PHP. All the page URLs became pretty.
                    From <code>iisi3.pl?cid=tussi&sid=180&mid=1</code> awfulness we went to <code>/en/page/subpage</code>.
                    I became an advocate of <em>beautifurls</em>, so I cleaned up the rest of fugly file urls too.
                </p>

                <p>
                    But the access control problem remained. It was hard to live with it, but we had to. Serving
                    all the files of all the customers with PHP wasn't an option with our machine specs, or at least
                    I did not consider it an option. Remember, there was not yet x-sendfile or any extensions like that.
                </p>

                <p>
                    When we got a customer whose files really, really <strong>needed to be secure</strong> I built
                    a file serving script. Apache routed the semi-pretty url to the script which then served the file
                    from inside PHP. The feature could be enabled per-customer basis and offered both security
                    and semi-pretty URLs. And the solution was heavier than necessary - <em>all</em> files, not just
                    those not world-readable, were served from PHP. I was not happy.
                </p>

                <p>
                    All problems were not technological. The customers uploaded HUGE images and then scaled it in the
                    WYSIWYG editor. Then they wondered why their web pages loaded slow. We then always instructed
                    them to scale their images to web size in Photoshop or Paint Shop Pro or such. Automatic
                    processing of uploaded stuff was clearly something that was needed in the CMS's file management v2.
                </p>

                <p>
                    I had many plans for the file management v2, but never got to it. There was so much to develop
                    and so little time. But all of it was forever in my head. Until it was time to part ways
                    with eMedia.
                </p>

                <h2>Age of the Mediabanksta</h2>

                <p>
                    The next age of Filebanksta was born within <a href="http://en.wikipedia.org/wiki/Igglo"></a>Igglo</a>,
                    a Finnish real estate broker with great ambitions. Great times, fun people. Really enjoyed
                    my time there.
                </p>

                <p>
                    One of my primary tasks there was to design a media bank application. Igglo's use case was very
                    clear: trillions of images of buildings and ads were gonna be uploaded. They had to be processed,
                    associated and quickly served to customers.
                </p>

                <p>
                    Thus, the idea of <em>publishing</em> in Filebanksta jargon was born. All files deemed world-viewable
                    by the app logic were published inside webscope, and they had a pretty URL. All files not world-viewable
                    (for example images in non-published ads) were rendered through the PHP framework. And to save
                    space, the files could be published via symbolic links if the publishing and storing of files
                    happeded to live in the same file system - which they did in Igglo's case.
                </p>

                <p>
                    One thing that didn't become clear as quickly as it should have was the physical storing of files in a
                    file system. filesystems have limitations concerning the number of stuff in the same directory.
                    It isn't enough to just store 500 files in a folder, if the directory structure doesn't have
                    enough levels. 50000 directories in a directory is just as bad as 50000 files. It had to be
                    learned the hard way, but the result was an idea of abstracting the calculation of directory ids
                    alltogether.
                </p>

                <p>
                    Later on we also needed other types of files to be uploaded - documents and so forth. The need
                    to differentiate files was born. My idea was to each uploaded file to have a <em>profile</em> - ie.
                    simple distinguishing metadata to separate their use cases from each other. The transformations
                    or non-transformations of the files could then be done depending on the files' profiles and
                    mime types.
                </p>

                <h2>The Emerald era</h2>

                <p>
                    In 2008 we decided to found a company with a couple of compatriots I knew from eMedia.
                    Inspired by the eMedia CMS we decided to compete within the custom-built simple CMS field.
                    We named our product "Emerald" and decided to built it with Zend Framework.
                </p>

                <p>
                    The company failed because of multiple reasons, but I kept developing Emerald the CMS for my
                    own pleasure. The version 3.0 was to be an open source, easily pluggable CMS (and general improvement)
                    package for any
                    Zend Framework application. But the more I developed it, the more I focused on the file
                    management. In time it pretty much got all the features I had envisioned for all the previous
                    media- and filebankstas.
                </p>

                <p>
                    When my interest in the whole Emerald "stack" waned, I extracted and forked the Filelib component.
                    Now it was an independent reusable file library component for Zend Framework applications,
                    and found it's way into production in a couple of large projects of myself, Brain Alliance
                    (my employer at that time), and even some of Brain Alliance's largest customers.
                </p>

                <p>
                    Some of the new projects used MongoDB and the rest used Doctrine, so it was time to abstract
                    away more Zend Framework stuff. Metadata could now be stored in MongoDB and files stored in
                    GridFS. These refactorings revealed more refactoring points so Filebanksta kept evolving.
                </p>

                <p>
                    Developing library with more actual live clients did good to Filebanksta.
                    It was still purely a Zend Framework library but at least the experiences easily proved the validity of
                    it's general and it's worth so I was happy.
                </p>


                <h2>Life in the Xi library</h2>

                <p>
                    Within Brain Alliance we created the "Xi" initiative.
                    A common namespace for any reusable open source component we created. It was logical to seed
                    the project with all things Filebanksta, so we did that.
                </p>

                <p>
                    The emergence of Symfony 2 helped us to get rid of the last remnants of Zend Framework
                    exclusivity. Filebanksta was now a truly reusable and loosely coupled component with
                    integration bridges for both Symfony and ZF.
                </p>

                <p>
                    Time kept passing, code kept flowing. For the last couple of years I've been planning to
                    "come out" with the library, but time has never been right. There's always been stuff
                    I'm not happy enough about. Tests were lacking. New features (video plugins, asynchronous
                    stuff) were always added. I also got very depressed and did not
                    code anything at all for half a year. And then, for a good while, development went into a very wrong direction
                    and much had to be fixed and rethought. Documentation has been nonexistent all the while.
                </p>

                <p>
                    It's certainly not been easy, but I think that Filebanksta is ready to presented
                    as it is, warts and all. At least I've learned a lot with it, even if nobody chooses to use it. So it's time
                    for thanks so far. If I forgot you, it's not personal.
                </p>

                <h3>People who contribute / have contributed</h3>

                <ul>
                    <li>Mikko Hirvonen</li>
                    <li>Martin Pärtel</li>
                    <li>Mikko Hämäläinen</li>
                    <li>Peter Hillerström</li>
                    <li>Sami Tikka</li>
                    <li>Janimatti Ellonen</li>
                    <li>Petri Heinonen</li>
                    <li>Jukka Hassinen</li>
                    <li>Heikki Naski</li>
                    <li>Ahti Ahde</li>
                    <li>Petri Koivula</li>
                    <li>Panu Leppäniemi</li>
                    <li>Joonas Pajunen</li>
                    <li>Henri Vesala</li>
                </ul>

                <h3>Companies who contribute / have contributed</h3>

                <ul>
                    <li><a href="https://fraktio.fi">Fraktio</a></li>
                    <li><a href="http://igglo.com">Igglo</a></li>
                    <li>Brain Alliance (doesn't exist any more)</li>
                    <li>eMedia Company Finland (doesn't exist any more)</li>
                </ul>






            </div>

        </div>


    </div>


    </html>


</html>


<?php
