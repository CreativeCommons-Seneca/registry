  <?php
  $uploaddir = 'uploads/';
    $file = $uploaddir . basename($_FILES['fileToUpload']['name']);

    // I was thinking of a large IF STATEMENT HERE ..

    # upload the file
    if (move_uploaded_file($_FILES['file']['tmp_name'], $file)) {
      $result = 1;
    } else {
      $result = 0;
    }

    sleep(10);
    echo $result;
    ?>