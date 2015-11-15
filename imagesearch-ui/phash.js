function phash(imageData, width, height){
  //Get image data from canvas data
  var data = imageData.data;

  // Image pixel arraay, [x][y]
  // Each x - element contains the corresponding y-coordinate    
  
  // Pre allocation of array for speed
  var xpixels = new Array();
  xpixels.length = width;
  for(x = 0; x < width; x++){
    xpixels[x] = new Array();
    xpixels[x].length = height;
  }

  var datalength = data.length;
  for(var i = 0; i < datalength; i += 4) {
    // Greyscale the image (using CImg method)
    brightness = (66*(data[i]) + 129*data[i+1] + 25*data[i+2] + 128)/256 + 16;

    var y = Math.floor(i/(4*width));
    var x = i/4 -(width*y);
    xpixels[x][y] = brightness;
  } 

  var canvasWidth  = width;
  var canvasHeight = height;

  //console.log(width, height);
        

  // Apply the convolve filter 
  // Using CIMg get_correlate() function with a 7x7 mask
  // Produces very accurate (identical to CImg) results on images tested

  var mx1 = 3, my1=3,mx2=3,my2 = 3, mz1=0, mz2=0;
  var mxe = width - mx2, mye = height-my2, mze = 1, z=0, zm, ym, xm;
  var y = 0;

  //var convolvearray = [[]];
  //convolvearray.length = width;

  var convolvearray = new Array();
  for(x = 0; x < width; x++){ 
    convolvearray[x] = new Array();   
    //convolvearray[x].length = mye;
  }


  for (z = mz1; z<mze; ++z){
    for (y = my1; y<mye; ++y){
      for (x = mx1; x<mxe; ++x) {
        val = 0;
        for (zm = -mz1; zm<=mz2; ++zm){
          for (ym = -my1; ym<=my2; ++ym){
            //Uses up alot of memory here
            for (xm = -mx1; xm<=mx2; ++xm){
              //val+=Math.trunc(xpixels[x+xm][y+ym]);                   
              val+=xpixels[x+xm][y+ym];
            }
          }
        }
        convolvearray[x][y] = val;
        val = 0;
      }
    }
  }

  var val = 0;
  y = 0;
  x = 0;

  // Apply the convolve filter to the boundary (edge pixels)
  for(y=0; y< height; y++){
    for (x = 0; x<width; (y<my1 || y>=mye || z<mz1 || z>=mze)?++x:((x<mx1-1 || x>=mxe)?++x:(x=mxe))) {
      val = 0;
      for ( zm = -mz1; zm<=mz2; ++zm){
        for ( ym = -my1; ym<=my2; ++ym){
          for ( xm = -mx1; xm<=mx2; ++xm){

            var k = x + xm;
            k = (k < 0)? 0: k;
            k = (k >= width)? width-1: k;
            var j = y + ym;
            j = (j<0)? 0: j;
            j = (j >= height)? height-1: j;

            //val=val+Math.trunc(xpixels[k][j]);
            val=val + xpixels[k][j];
          }
        }
      }

      convolvearray[x][y] = val;          
      val = 0;
    }
  }


  var resized = new Array();
  for(x=0; x < 32; x++){
    resized[x] = new Array();
  }
  var xr = width/32;
  var yr = height/32;
  for(x=0;x<32;x++){
    for(y=0;y<32;y++){
      resized[x][y] = convolvearray[Math.trunc(x*xr)][Math.trunc(y*yr)];
    }
  }


  //Create the matrix used in multiplication (contstant)
  var matrix = new Array();

  for(x=0; x < 32; x++){
    matrix[x] = new Array();
  }

  var c1 = Math.sqrt(2.0/32); 

  for (x=0;x<32;x++){
    for (y=0;y<32;y++){
      if(y === 0){
        matrix[x][y] = 1/Math.sqrt(32);
      }
      else{
        matrix[x][y] = c1*Math.cos((Math.PI/2/32)*y*(2*x+1));
      } // close if
    }
  }// close for loops

  // Transpose the matrix, used for multiplication - permute_axes (CIMg function)
  var transpose = new Array();
  for(x=0; x < 32; x++){
    transpose[x] = new Array();
  } 

  for(x=0; x < 32; x++){
    for(y=0;  y < 32; y++){
      transpose[x][y] = matrix[y][x];
    }   
  }

  var result = new Array();
  for(x=0; x < 32; x++){
    result[x] = new Array();
  }

  // Multiply matrix and convolve image
  // This produces slightly skewed results as it is hard to pinpoint the precision with floats
  var total =0;
  for (i=0;i<32;i++){
    for (j=0;j<32;j++){
      total = 0;
      for(k=0; k < 32; k++){
          //total+=matrix[k][j]*convolvearray[i][k];
          total+=matrix[k][j]*resized[i][k];
        }
        result[i][j] = total;
      } 
    }


  // Mulptiply the result by transposed matrix
  var result2 = new Array();
  for(x=0; x < 32; x++){
    result2[x] = new Array();
  }
  var total =0;
  for (i=0;i<32;i++){
    for (j=0;j<32;j++){
      total = 0;
      for(k=0; k < 32; k++){
        total+=result[k][j]*transpose[i][k];
      }
      result2[i][j] = total;
    } 
  }

  hashimage = new Array();
  hashmedian = new Array();


  // Getting the hash image - crop from (1,1) to (9,9)
  // and unroll values linearly along x axis
  // using crop() and unroll(x) funcitons in CImg library
  for(y=1; y < 9; y++){
    for(x=1; x < 9; x++){
      hashimage.push(result2[x][y]);
      hashmedian.push(result2[x][y]);
    }
  }

  console.log(" ******************* HASH ************");
  //console.log(hashimage);

  // Get the median of the hashimage
  var median = median(hashmedian);

  function median(values) {
    values.sort( function(a,b) {return a - b;} );
    var half = Math.floor(values.length/2);
    if(values.length % 2)
      return values[half];
    else
      return (values[half-1] + values[half]) / 2.0;
  }

  var hashold = dctHash(hashimage, median);

  // Hash the image depending on median variance
  function dctHash(hashimage, median){
    return hashimage.map(function(e){
      return e > median ? '1' : '0';
    }).join('');
  }


  var hash = reverse(hashold);

  // Reverse the endian-ness
  function reverse(s) {
    var o = '';
    for (var i = s.length - 1; i >= 0; i--)
      o += s[i];
    return o;
  }
  console.log(hash);

  var hex = binaryToHex(hash);
  hex = "0x" + hex.result;
  
  console.log("HEX");
  console.log(hex);

  // Binary to Hex function
  function binaryToHex(s) {
    var i, k, part, accum, ret = '';
    for (i = s.length-1; i >= 3; i -= 4) {
          // extract out in substrings of 4 and convert to hex
          part = s.substr(i+1-4, 4);
          accum = 0;
          for (k = 0; k < 4; k += 1) {
            if (part[k] !== '0' && part[k] !== '1') {
                  // invalid character
                  return { valid: false };
                }
              // compute the length 4 substring
              accum = accum * 2 + parseInt(part[k], 10);
            }
            if (accum >= 10) {
              // 'A' to 'F'
              ret = String.fromCharCode(accum - 10 + 'A'.charCodeAt(0)) + ret;
            } else {
              // '0' to '9'
              ret = String(accum) + ret;
            }
          }
      // remaining characters, i = 0, 1, or 2
      if (i >= 0) {
        accum = 0;
          // convert from front
          for (k = 0; k <= i; k += 1) {
            if (s[k] !== '0' && s[k] !== '1') {
              return { valid: false };
            }
            accum = accum * 2 + parseInt(s[k], 10);
          }
          // 3 bits, value cannot exceed 2^3 - 1 = 7, just convert
          ret = String(accum) + ret;
        }
        return { valid: true, result: ret };
    }; // close bin to hex function
    
    return hex;
  }



