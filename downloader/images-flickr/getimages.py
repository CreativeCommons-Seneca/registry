"""

getimages.py

  By: Anna Fatsevych
  Date: May 19, 2015

  Program to download images using flickr API (flickr.py) for Python
  Uses date and license parameters at the command line
  First argument is license(s): could be a list of multiples: 1,2,3
  Second arg is the date in "YYYY-MM-DD" format

"""


import flickr
import urllib, urlparse
import os
import sys
from timeit import default_timer as timer

start = timer()

if len(sys.argv)>1:
  lic = sys.argv[1]
  date = sys.argv[2]
else:
  print 'no tag specified'
firstattempt = 0
# downloading image data
#f = flickr.people_getPublicPhotos(min_upload_date="2015-03-20", max_upload_date="2015-03-20", license=lic)
while firstattempt < 3:
  try:
    #get the photos
    f = flickr.photos_search(min_upload_date=date, max_upload_date=date, license=lic, per_page="500")
    #get the total pages
    fn = flickr.photos_search_pages(min_upload_date=date, max_upload_date=date, license=lic, per_page="500")
    #loop through the pages
    print 'TOTAL', fn, len(f)
    for z in range(0,int(fn)):
      pageattempts=0
      while pageattempts < 3:
        try:
          f = flickr.photos_search(min_upload_date=date, max_upload_date=date, license=lic, page=z+1, per_page="500")
          #print 'license:', lic
          urllist = [] #store a list of what was downloaded
          fl = open('urllist.txt', 'w')
          fail = open('failed.txt', 'w')
          counter = 0
          attempts = 0
          # downloading images
          for k in f:
              try:
                url = k.getURL(size='Original', urlType='source')
                urllist.append(url)
                print 'Photo ID:', k.id
                print 'license: ' , k.license,counter, k.datetaken
                k._load_properties()
                #(k.owner)._load_properties()
                print 'Title: ', k.title, k.dateposted
                print 'user' , k.owner.realname,
                print 'author', k.owner, k.owner.username
                urlcredit=('https://www.flickr.com/photos/' + str(k.owner.id) + '/'+k.id + 'Owner Real Name: ' + str(k.owner.realname)+'License: ' + str(k.license))
                #counter=counter+1
                image = urllib.URLopener()
                attempts = 0
                while attempts < 3:
                  try:
                    image.retrieve(url, os.path.basename(urlparse.urlparse(url).path))
                    fl.write(urlcredit+'\n')
                    print 'downloading: ' + str(url) + 'Page: ' + str(z+1) + 'File:' + str(counter)
                    print 'writing to DOWNLOAD file:' , urlcredit
                    attempts = 4
                    counter +=1
                  except KeyboardInterrupt:
                    pass
                    raise
                  except:
                    attempts +=1
                    if attempts == 2:
                      fail.write(urlcredit+'\n')
                      print '***********Writing to file!'
                    print 'URL DOWNLOAD FAIL!!!'
              except KeyboardInterrupt:
                raise
              except:
                fail.write(k.id+'\n')
                print '****************** FAILED TO GET URL!!!!'
                attempts=4
          pageattempts=4
        except KeyboardInterrupt:
          raise
        except:
          raise
          print '******************* PAGE:' + str(z) + 'FAILED TO LOAD'
          pageattempts+=1
    fl.close()
    fail.close()
    firstattempt = 4
  except KeyboardInterrupt:
    raise
    firstattempt=4
  except:
    raise
    firstattempt +=1
    print '*************************   First Attempt Failed TRY: ', firstattempt
elapsed_time = (timer() - start)/60
print'TIME:', elapsed_time


