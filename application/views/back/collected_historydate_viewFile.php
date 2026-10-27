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
                <?php if (!empty($user_collect_historyDate[0]->time_s_s)) { ?>
                    <table border="1">
                        <tr>
                            <th>SL</th>
                            <th>Date</th>
                            <th>HoldingNo</th>
                            <th>HoldingName</th>
                            <th>Amount</th>
                        </tr>
                        <?php $tAmnt=[]; $sl=1; foreach ($user_collect_historyDate as $info) { ?>
                            <tr>
                                <td align="center" ><?= $sl ; ?></td>
                                <td>
                                    <?= date('d-m-Y', $info->time_s_s);?><br>
                                    <?= date('h:i:sa', $info->time_s_s);?>
                                </td>
                                <td align="center" ><?= $info->holding_number ; ?></td>
                                <td><?= $info->family_person_name_head ; ?></td>
                                <td align="right" style="font-size: 16px; font-weight: bold;  "><?= $tAmnt[] = $info->amnt_taka ; ?>/-</td>
                            </tr>
                        <?php $sl++;} ?>
                        <tr>
                            <td colspan="4" align="right" style="font-size: 20px; font-weight: bold;  " >Total</td>
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