# images-flickr
getImages-Flickr

The program will download the images to the pwd, and will write the downloaded photo's credit url to the urllist.txt file, and if any fail to download the photo url will be written to failed.txt

To run getImagesByDate.py

    $ python getImagesByDate.py 2 2015-03-20

The program will get all the images with License 2 uploaded on 2015-03-20
You may specify multiple Licenses 

    $ python getImagesByDate.py 1,2,3 2015-03-20 

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

