<?php
if (isset($_POST['submit'])) {
    $date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_STRING);

    // Checking if date is not null
    if (!empty($date)) {

        $todays_date = date('Y-m-d');

        // Minimum date user can pick
        $min_date = date('Y-m-d', (strtotime('-2 years', strtotime($todays_date))));

        // name of the uploaded file
        $filename = $_FILES['myfile']['name'];

        // Get uploaded file extension
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        // The physical file on a temporary uploads directory on the server
        $file = $_FILES['myfile']['tmp_name'];

        // Checking date
        if ($date > $min_date && $date <= $todays_date) {
            // Checking file extension
            if ($_FILES['myfile']['size'] > 1000000) {
                $_SESSION['upload_alert'] = 'File should not be larger than 1Megabyte';
            } else {
                $filename = $_SESSION["data"]['eid'] . '_' . $cert_name .'.'. $extension;

                $destination = '/var/www/certificates/' . $cert_name . '/' . $filename;

                // Checking if file upload was complited

                if (move_uploaded_file($file, $destination)) {


                        if ($cert_name == '1') {
                            
                            $access_update_query = $db->prepare('UPDATE access SET training_date1=:date, status1= 1 WHERE eid=:eid;');
                            // if user have access to laboratory and upload certificates -> renewing process
                            if ($areq['status'] != 0 && $areq['status'] != 4) {
                                $active_user_update = $db->prepare('UPDATE active_requests SET status=2, upload_cer_time=NULL WHERE eid=:eid');
                                $active_user_update->bindValue(':eid', $_SESSION["data"]['eid'], PDO::PARAM_STR);
                                $active_user_update->execute();
                            }
                            


                        } else {
                            
                            $access_update_query = $db->prepare('UPDATE `access` SET `training_date2`=:date, `status2`= 1 WHERE `eid`=:eid;');
                        }

                        $access_update_query->bindValue(':eid', $_SESSION["data"]['eid'], PDO::PARAM_STR);
                        $access_update_query->bindValue(':date', $date, PDO::PARAM_STR);
                        $access_update_query->execute();
                        header("Location: /");
                        exit();

                } else {
                    $_SESSION['upload_alert'] = 'File upload fail';
                }
            }
        } else {
            $_SESSION['upload_alert'] = 'Date is incorect!';
        }
    } else {
        $_SESSION['upload_alert'] = 'You didn\'t pick the date!';
    }
} else {
}
header("Location: /upload/cert/" . $cert_name);

