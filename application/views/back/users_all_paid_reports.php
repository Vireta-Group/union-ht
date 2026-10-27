<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Collect Report</title>
    </head>
    <body>
        <center>
            <h1 style="margin: 10px 0 0 0; padding: 0; ">
                <?= $setting_info->ins_name; ?>
            </h1> 
            <h3 style="margin: 0; padding: 0; ">
                <?= $setting_info->ins_title_ss; ?>
            </h3> 
            <h3 style="margin: 0; padding: 0; ">
                <?= $setting_info->up_bn_name; ?>, <?= $setting_info->dist_bn_name; ?>
            </h3> 
            <h4 style="margin: 0; padding: 0; ">
                Date <?= date('d-m-Y', strtotime($_GET['sDate'])); ?> to <?= date('d-m-Y', strtotime($_GET['eDates'])); ?>
            </h4> 
        </center>
        <center>
            <?php if ($paids_info) { ?>
                <table border="1" cellspacing="0" style="margin: 25px 0 20px 0;" >
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>User</th>
                        <th>Amount</th>
                    </tr>
                    <?php $sl = 1; foreach ($paids_info as $collect) {  ?> 
                        <tr> 
                            <td align="center" ><?= $sl; ?></td>
                            <td><?= date('d-m-Y', $collect->timesingstime) ; ?></td> 
                            <td><?= $collect->user_person_name; ?></td>
                            <td align="right" style="font-size: 18px;" ><b><?= $collect->usr_amnt_cuttt; ?>/-</b></td>
                        </tr> 
                    <?php $sl++;  } ?> 
                </table> 
            <?php }else { ?> 
                <h2> No Collection </h2> 
            <?php } ?> 
        </center>
    </body>
</html>