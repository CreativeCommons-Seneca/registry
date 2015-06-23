
#Image Downloader in PHP - flickr.php

Dependencies:

    $ sudo apt-get install apache2
    $ sudo apt-get install php5 libapache2-mod-php5
    
    $ sudo apt-get install php5-curl
    
    $ sudo apt-get install php5-cli php5-dev make
    $ sudo apt-get install libsqlite3-0 libsqlite3-dev
    $ sudo apt-get install php5-sqlite
    
    


Put all the files from the phpflickr-master folder in the same directory.
To run use command line:

    $ php flickr.php 2015-03-20 2015-04-20
    
It will download all the images from Flickr uploaded on the specified date, with all the applicable CC Licenses.  Uses phpFlickr.php API -  https://github.com/dan-coulter/phpflickr

A new sqLite database will be created, aswell as the text file with all the information about downloaded images.

#Image Downloader in Python - getimages.py


The program will download the images to the pwd, and will write the downloaded photo's credit url to the urllist.txt file, and if any fail to download the photo url will be written to failed.txt

To run getimages.py

    $ python getimages.py 2 2015-03-20

The program will get all the images with License 2 uploaded on 2015-03-20
You may specify multiple Licenses 

    $ python getimages.py 1,2,3 2015-03-20 

The list of Licenses from FLickr - available when using the api call flickr.photos.licenses.getInfo

    <license id="0" name="All Rights Reserved" url="" />
    <license id="1" name="Attribution-NonCommercial-ShareAlike License" url="http://creativecommons.org/licenses/by-nc-sa/2.0/" />
    <license id="2" name="Attribution-NonCommercial License" url="http://creativecommons.org/licenses/by-nc/2.0/" />
    <license id="3" name="Attribution-NonCommercial-NoDerivs License" url="http://creativecommons.org/licenses/by-nc-nd/2.0/" />
    <license id="4" name="Attribution License" url="http://creativecommons.org/licenses/by/2.0/" />
    <license id="5" name="Attribution-ShareAlike License" url="http://creativecommons.org/licenses/by-sa/2.0/" />
    <license id="6" name="Attribution-NoDerivs License" url="http://creativecommons.org/licenses/by-nd/2.0/" />
    <license id="7" name="No known copyright restrictions" url="http://flickr.com/commons/usage/" />
    <license id="8" name="United States Government Work" url="http://www.usa.gov/copyright.shtml" />

