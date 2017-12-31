<?php

if ($_POST['action'] == 'submit') {

  $servername = "xxx";
  $username = "xxx";
  $password = "xxx";
  $dbname = "xxx";

  $conn = mysqli_connect($servername, $username, $password, $dbname);

  if (!$conn) {
    echo "Connection failed: " . mysqli_connect_error();
  } else {
    mysqli_set_charset($conn, "utf8");
  }

  $fillerName = mysqli_real_escape_string($conn, $_POST['fillerName']);
  $fillerPhone = mysqli_real_escape_string($conn, $_POST['fillerPhone']);
  $groupName = mysqli_real_escape_string($conn, $_POST['groupName']);
  $week = mysqli_real_escape_string($conn, $_POST['week']);

  $decodeNames = json_decode($_POST['names'], TRUE);
  $decodeTaken = json_decode($_POST['taken'], TRUE);
  $decodeReturned = json_decode($_POST['returned'], TRUE);
  $decodePaid = json_decode($_POST['paid'], TRUE);

  $trimNames = array_map(array($conn, 'real_escape_string'), $decodeNames);
  $names = array_map('trim', $trimNames);

  $trimTaken = array_map(array($conn, 'real_escape_string'), $decodeTaken);
  $taken = array_map('trim', $trimTaken);

  $trimReturned = array_map(array($conn, 'real_escape_string'), $decodeReturned);
  $returned = array_map('trim', $trimReturned);

  $trimPaid = array_map(array($conn, 'real_escape_string'), $decodePaid);
  $paid = array_map('trim', $trimPaid);

  $max = count($names);
  $successCounter = count($names);

  for ($counter = 0; $counter < $max; $counter++) {

    $sqlSellers = "INSERT INTO sellers (week, fillerName, fillerPhone, groupName, name, taken, returned, paid) VALUES ('$week', '$fillerName', '$fillerPhone', '$groupName', '$names[$counter]', '$taken[$counter]', '$returned[$counter]', '$paid[$counter]')";

    if (!mysqli_query($conn, $sqlSellers)) {
      echo("Error description: " . mysqli_error($conn));
    } else {
      $successCounter = $successCounter - 1;
    }
  };

  if ($successCounter == 0) {
    echo 'success';
  }

  mysqli_close($conn);

} else if ($_POST['action'] == 'loadInfomation') {

  $servername = "xxx";
  $username = "xxx";
  $password = "xxx";
  $dbname = "xxx";

  $conn = mysqli_connect($servername, $username, $password, $dbname);

  if (!$conn) {
    echo "Connection failed: " . mysqli_connect_error();
  } else {
    mysqli_set_charset($conn, "utf8");
  }

  $groupsNotEscaped = array('koira', 'ilveksetTytöt', 'ilveksetPojat', 'puumat', 'lumikot', 'sudet', 'nallekarkit', 'harmaakarhut', 'saigat', 'puuppelit', 'johtajat');
  $groups = array_map(array($conn, 'real_escape_string'), $groupsNotEscaped);
  //echo $groups[2];

  $return = array();

  function search($i) {
    global $conn, $return, $groups;
    $sqlSearch = "SELECT name, taken, returned, paid FROM sellers WHERE groupName = '$groups[$i]'";
    $result = mysqli_query($conn, $sqlSearch);
    $rowCount = mysqli_num_rows($result);
    $temporaryArray = array();
    $temporaryNames = array();
    $temporaryNamesUnique = array();
    $temporaryTaken = 0;
    $temporaryReturned = 0;
    $temporaryPaid = 0;

    if (!$result) {

      echo 'mysqli_query error: ' . mysqli_error($conn);

    } else {

      if ($rowCount > 0) {

        $counter = 0;

        while (($row = mysqli_fetch_assoc($result)) && ($counter < $rowCount)) {
          array_push($temporaryNames, $row['name']);
          $temporaryTaken = $temporaryTaken + $row['taken'];
          $temporaryReturned = $temporaryReturned + $row['returned'];
          $temporaryPaid = $temporaryPaid + $row['paid'];
          $counter++;
        }

      } else {
        array_push($temporaryNames, 'exampleName');
      }
    }

    $temporaryNamesUnique = array_unique($temporaryNames);

    if ($temporaryNames == ['exampleName']) {
      unset($temporaryNames);
      $temporaryNames = array();
    }

    array_push($temporaryArray, count($temporaryNamesUnique), $temporaryTaken, $temporaryReturned, $temporaryPaid);
    array_push($return, $temporaryArray);

    unset($temporaryArray);
    unset($temporaryNames);
    unset($temporaryNamesUnique);
  }

  for ($i = 0; $i < count($groups); $i++) {
    search($i);
  }

  $JSONreturn = json_encode($return);
  echo $JSONreturn;

  mysqli_close($conn);

} else if ($_POST['action'] == 'loadGroup') {

  $servername = "xxx";
  $username = "xxx";
  $password = "xxx";
  $dbname = "xxx";

  $conn = mysqli_connect($servername, $username, $password, $dbname);

  if (!$conn) {
    echo "Connection failed: " . mysqli_connect_error();
  } else {
    mysqli_set_charset($conn, "utf8");
  }

  $return = array();
  $group = $_POST['group'];
  $names = array();
  $counter = 0;

  $sqlNameSearch = "SELECT name FROM sellers WHERE groupName = '$group'";
  //$sqlGroupSearch = "SELECT taken, returned, paid FROM sellers WHERE name = '$names[$counter]'";
  $resultNames = mysqli_query($conn, $sqlNameSearch);
  //$resultGroups = mysqli_query($conn, $sqlGroupSearch);
  $rowCountNames = mysqli_num_rows($resultNames);
  //$rowCountGroups = mysqli_num_rows($resultGroups);

  if (!$resultNames) {

    echo 'mysqli_query error: ' . mysqli_error($conn);

  } else {

    if ($rowCountNames > 0) {

      $counter = 0;

      while (($rowNames = mysqli_fetch_assoc($resultNames)) && ($counter < $rowCountNames)) {
        array_push($names, $rowNames['name']);
        $counter++;
      }

      $trimNames = array_map('trim', $names);
      $uniquenames = array_unique($trimNames);
      $indexReset = array_values($uniquenames);
      $namesLength = count($indexReset);


      for ($i = 0; $i < $namesLength; $i++) {

        $sqlGroupSearch = "SELECT taken, returned, paid FROM sellers WHERE name = '$indexReset[$i]'";
        $resultGroups = mysqli_query($conn, $sqlGroupSearch);
        $rowCountGroups = mysqli_num_rows($resultGroups);

        $counter = 0;
        $temporaryTaken = 0;
        $temporaryReturned = 0;
        $temporaryPaid = 0;
        $temporaryArray = array();

        while (($rowGroups = mysqli_fetch_assoc($resultGroups)) && ($counter < $rowCountGroups)) {
          $temporaryTaken = $temporaryTaken + $rowGroups['taken'];
          $temporaryReturned = $temporaryReturned + $rowGroups['returned'];
          $temporaryPaid = $temporaryPaid + $rowGroups['paid'];
          $counter++;
        }

        array_push($temporaryArray, $indexReset[$i], $temporaryTaken, $temporaryReturned, $temporaryPaid);
        array_push($return, $temporaryArray);

        unset($temporaryArray);

      }

      $JSONreturn = json_encode($return);

      echo $JSONreturn;


    } else {
      echo json_encode('no sellers');
    }
  }


} else {
  echo 'action not defined';
}

?>
