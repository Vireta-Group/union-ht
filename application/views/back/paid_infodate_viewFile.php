<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title></title>
    </head>
    <body>
        <div class="" style="" >
            <center>
                <div class="" style="" >
                    <h1 style="margin: 0; padding: 0; " > <?= $user_infos->user_person_name; ?> <span style="font-size: 24px; " ><sup> (<?= $user_infos->percentager; ?>%) </sup></span> </h1>
                    <h4 style="margin: 0; padding: 0; " > <?= $user_infos->name; ?> </h4>
                    <h4 style="margin: 0; padding: 0; " > <?= $user_infos->user_phone_no; ?> </h4>
                </div>
            </center>
            <center style="margin-top: 15px; " >
                <?php if (!empty($userPaidInfoDate[0]->timesingstime)) { ?>
                    <table border="1">
                        <tr>
                            <th>SL</th>
                            <th>Date</th>
                            <th>Amount</th>
                        </tr>
                        <?php $tAmnt=[]; $sl=1; foreach ($userPaidInfoDate as $info) { ?>
                            <tr>
                                <td align="center" ><?= $sl ; ?></td>
                                <td>
                                    <?= date('d-m-Y', $info->timesingstime);?><br>
                                    <?= date('h:m:sa', $info->timesingstime);?>
                                </td>
                                <td align="right" style="font-size: 16px; font-weight: bold;  "><?= $tAmnt[] = $info->usr_amnt_cuttt ; ?>/-</td>
                            </tr>
                        <?php $sl++;} ?>
                        <tr>
                            <td colspan="2" align="right" style="font-size: 20px; font-weight: bold;  " >Total</td>
                            <td align="right" style="font-size: 20px; font-weight: bold;  "><?= array_sum($tAmnt);?> /-</td>
                        </tr>
                    </table>
                <?php } else { ?>
                    <h3> No Collect Found </h3>
                <?php } ?>
            </center>
        </div>
    </body>
</html>