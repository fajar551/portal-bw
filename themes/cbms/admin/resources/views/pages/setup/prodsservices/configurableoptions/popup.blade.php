<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ Cfg::getValue('CompanyName') }} -  Configurable Options</title>
    @include('includes.style')
</head>

<body>
    @if(!$cid)
    <div class="container-fluid">
        <form action="" id="manageConfigurable" method="post" enctype="multipart/form-data">
            <div class="card pt-5 pb-5">
                <div class="card-body">
                    <h2 class="mb-5 text-center">Configurable Options</h2>
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <div class="form-group row">
                                <label for="" class="col-sm-4">Option Name</label>
                                <div class="col-sm-8">
                                    <input type="text" name="configoptionname" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group row">
                                <label class="col-sm-4">Option Type:</label>
                                <div class="col-sm-8">
                                    <select name="configoptiontype" class="form-control">
                                        <option value="1">Dropdown</option>
                                        <option value="2">Radio</option>
                                        <option value="3">Yes/No</option>
                                        <option value="4">Quantity</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>


                    <table class="table" id="tableloadfrom">
                        <thead>
                            <tr>
                                <th>Option</th>
                                <th>One Time/Monthly</th>
                                <th>Semi-Annual</th>
                                <th>Biennial</th>
                                <th>Triennial</th>
                                <th>Order</th>
                                <th>Hide</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="form-group row">
                                        <label for="" class="col-3">Add Option:</label>
                                        <div class="col-9">
                                            <input type="text" name="addoptionname" class="form-control">
                                        </div>
                                    </div>
                                </td>
                                <td>

                                </td>
                                <td>

                                </td>
                                <td>

                                </td>

                                <td>

                                </td>
                                <td>
                                    <div class="form-group">
                                        <input type="text" name="addsortorder" value="0" class="form-control" style="width: 60px;">
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group">
                                        <input type="checkbox" name="addhidden" value="1">
                                    </div>
                                </td>
                            </tr>

                        </tbody>
                    </table>

                    <div class="pt-4">
                        {{ csrf_field() }}
                        <input type="hidden" value="{{ $gid }}" name="gid" />
                        <button type="submit" class="btn btn-primary">Save changes</button>
                        <button type="button" class="btn btn-danger" onclick="closewindow();">Close</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    @else
    <div class="container-fluid">
        <form action="" id="manageConfigurable" method="post" enctype="multipart/form-data">
            <div class="card pt-5 pb-5">
                <div class="card-body">
                    <h2 class="mb-5 text-center">Configurable Options</h2>
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <div class="form-group row">
                                <label for="" class="col-sm-4">Option Name</label>
                                <div class="col-sm-8">
                                    <input type="text" value="{{ $data->optionname }}" name="configoptionname" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group row">
                                <label class="col-sm-4">Option Type:</label>
                                <div class="col-sm-8">
                                    <select name="configoptiontype" class="form-control">
                                        <option value="1" {{ ($data->optiontype == 1)?'selected':'' }}>Dropdown</option>
                                        <option value="2" {{ ($data->optiontype == 2)?'selected':'' }}>Radio</option>
                                        <option value="3" {{ ($data->optiontype == 3)?'selected':'' }}>Yes/No</option>
                                        <option value="4" {{ ($data->optiontype == 1)?'selected':'' }}>Quantity</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <table class="table" id="tableloadfrom">
                        <thead>
                            <tr style="text-align:center;font-weight:bold;">
                                <th>Options</th>
                                <th width="70">&nbsp;</th>
                                <th width="70">&nbsp;</th>
                                <th width="70">&nbsp;</th>
                                <th width="70">One Time/<br>Monthly</td>
                                <th width="70">Quarterly</th>
                                <th width="70">Semi-Annual</th>
                                <th width="70">Annual</th>
                                <th width="70">Biennial</th>
                                <th width="70">Triennial</th>
                                <th width="80">Order</th>
                                <th width="30">Hide</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $optiontype=$data->optiontype;
                                $x=0;

                                foreach($config as $r){
                                    $x++;
                                    $optionid   = $r["id"];
                                    $optionname = $r["optionname"];
                                    $sortorder  = $r["sortorder"];
                                    $hidden     = $r["hidden"];
                                    $totalcurrencies = (count($r['currency']) * 2 );
                                    echo "<tr bgcolor=\"#ffffff\" style=\"text-align:center;\">
                                    <td rowspan=\"" . $totalcurrencies . "\"><input type=\"text\" name=\"optionname[" . $optionid . "]\" value=\"" . $optionname . "\" class=\"form-control\" style=\"min-width:180px;\">";
                                    if (1 < $x) {
                                        echo "<br><a href=\"#\" onclick=\"deletegroupoption('" . $optionid . "');return false;\"><img src=\"images/icons/delete.png\" border=\"0\">";
                                    }
                                    echo "<tr bgcolor=\"#ffffff\" style=\"text-align:center;\">
                                        <td rowspan=\"" . $totalcurrencies . "\"><input type=\"text\" name=\"optionname[" . $optionid . "]\" value=\"" . $optionname . "\" class=\"form-control\" style=\"min-width:180px;\">";
                                    if (1 < $x) {
                                        echo "<br><a href=\"#\" onclick=\"deletegroupoption('" . $optionid . "');return false;\"><img src=\"images/icons/delete.png\" border=\"0\">";
                                    }
                                    echo "</td>";
                                    $firstcurrencydone = false;
                                    foreach($r['currency'] as $curr_code => $val){
                                        $curr_id=$val['curr_id'];
                                        if ($firstcurrencydone) {
                                            echo "</tr><tr bgcolor=\"#ffffff\" style=\"text-align:center;\">";
                                        }
                                        echo "<td rowspan=\"2\" bgcolor=\"#efefef\"><b>" . $curr_code . "</b></td><td>Setup</td><td><input type=\"text\" name=\"price[" . $curr_id . "][" . $optionid . "][1]\" value=\"" . $val[1] . "\" class=\"form-control\" style=\"width:80px;\"></td><td><input type=\"text\" name=\"price[" . $curr_id . "][" . $optionid . "][2]\" value=\"" . $val[2] . "\" class=\"form-control\" style=\"width:80px;\"></td><td><input type=\"text\" name=\"price[" . $curr_id . "][" . $optionid . "][3]\" value=\"" . $val[3] . "\" class=\"form-control\" style=\"width:80px;\"></td><td><input type=\"text\" name=\"price[" . $curr_id . "][" . $optionid . "][4]\" value=\"" . $val[4] . "\" class=\"form-control\" style=\"width:80px;\"></td><td><input type=\"text\" name=\"price[" . $curr_id . "][" . $optionid . "][5]\" value=\"" . $val[5] . "\" class=\"form-control\" style=\"width:80px;\"></td><td><input type=\"text\" name=\"price[" . $curr_id . "][" . $optionid . "][11]\" value=\"" . $val[11] . "\" class=\"form-control\" style=\"width:80px;\"></td>";
                                        if (!$firstcurrencydone) {
                                            echo "<td rowspan=\"" . $totalcurrencies . "\"><input type=\"text\" name=\"sortorder[" . $optionid . "]\" value=\"" . $sortorder . "\" class=\"form-control\" style=\"width:60px;\"></td><td rowspan=\"" . $totalcurrencies . "\"><input type=\"checkbox\" name=\"hidden[" . $optionid . "]\" value=\"1\"";
                                            if ($hidden) {
                                                echo " checked";
                                            }
                                            echo " /></td>";
                                        }
                                        echo "</tr><tr bgcolor=\"#ffffff\" style=\"text-align:center;\"><td>Pricing</td><td><input type=\"text\" name=\"price[" . $curr_id . "][" . $optionid . "][6]\" value=\"" . $val[6] . "\" class=\"form-control\" style=\"width:80px;\"></td><td><input type=\"text\" name=\"price[" . $curr_id . "][" . $optionid . "][7]\" value=\"" . $val[7] . "\" class=\"form-control\" style=\"width:80px;\"></td><td><input type=\"text\" name=\"price[" . $curr_id . "][" . $optionid . "][8]\" value=\"" . $val[8] . "\" class=\"form-control\" style=\"width:80px;\"></td><td><input type=\"text\" name=\"price[" . $curr_id . "][" . $optionid . "][9]\" value=\"" . $val[9] . "\" class=\"form-control\" style=\"width:80px;\"></td><td><input type=\"text\" name=\"price[" . $curr_id . "][" . $optionid . "][10]\" value=\"" . $val[10] . "\" class=\"form-control\" style=\"width:80px;\"></td><td><input type=\"text\" name=\"price[" . $curr_id . "][" . $optionid . "][12]\" value=\"" . $val[12] . "\" class=\"form-control\" style=\"width:80px;\"></td>";
                                        $firstcurrencydone = true;

                                    }
                                    echo "</tr>";
                                    if ($optiontype == "1" || $optiontype == "2" || $x == "0") {
                                        echo "<tr bgcolor=\"#efefef\"><td colspan=\"9\"><B>Add Option:</B> <input type=\"text\" name=\"addoptionname\" class=\"form-control\" style=\"display:inline-block;width:60%;\"></td><td><input type=\"text\" name=\"addsortorder\" value=\"0\" class=\"form-control\" style=\"width:60px;\"></td><td><input type=\"checkbox\" name=\"addhidden\" value=\"1\" /></td></tr>\n";
                                    }

                                    $x++;
                                }



                            ?>
                        </tbody>
                    </table>


                    <div class="pt-4">
                        {{ csrf_field() }}
                        @method('PUT')
                        <input type="hidden" value="{{ $cid }}" name="cid" />
                        <button type="submit" class="btn btn-primary">Save changes</button>
                        <button type="button" class="btn btn-danger" onclick="closewindow();">Close</button>
                    </div>
                </div>
            </div>
        </form>
    </div>




    @endif




    @include('includes.scripts-global')
    <script type="text/javascript">
        function closewindow() {
            window.close();
            if (window.opener && !window.opener.closed) {
            window.opener.location.reload();
            }
        }
    </script>
</body>

</html>

<!--

<table class="table">
    <tbody>
        
        <tr bgcolor="#efefef" style="text-align:center;font-weight:bold;">
            <td>Options</td>
            <td width="70">&nbsp;</td>
            <td width="70">&nbsp;</td>
            <td width="70">One Time/<br>Monthly</td>
            <td width="70">Quarterly</td>
            <td width="70">Semi-Annual</td>
            <td width="70">Annual</td>
            <td width="70">Biennial</td>
            <td width="70">Triennial</td>
            <td width="80">Order</td>
            <td width="30">Hide</td>
        </tr>
        <tr bgcolor="#ffffff" style="text-align:center;">
            <td rowspan="6"><input type="text" name="optionname[153]" value="Account|Akun" class="form-control" style="min-width:180px;"></td>
            <td rowspan="2" bgcolor="#efefef"><b>IDR</b></td>
            <td>Setup</td>
            <td><input type="text" name="price[1][153][1]" value="0.00" class="form-control" style="width:80px;"></td>
            <td><input type="text" name="price[1][153][2]" value="0.00" class="form-control" style="width:80px;"></td>
            <td><input type="text" name="price[1][153][3]" value="0.00" class="form-control" style="width:80px;"></td>
            <td><input type="text" name="price[1][153][4]" value="0.00" class="form-control" style="width:80px;"></td>
            <td><input type="text" name="price[1][153][5]" value="0.00" class="form-control" style="width:80px;"></td>
            <td><input type="text" name="price[1][153][11]" value="0.00" class="form-control" style="width:80px;"></td>
            <td rowspan="6"><input type="text" name="sortorder[153]" value="0" class="form-control" style="width:60px;"></td>
            <td rowspan="6"><input type="checkbox" name="hidden[153]" value="1"></td>
        </tr>
        <tr bgcolor="#ffffff" style="text-align:center;">
            <td>Pricing</td>
            <td><input type="text" name="price[1][153][6]" value="0.00" class="form-control" style="width:80px;"></td>
            <td><input type="text" name="price[1][153][7]" value="0.00" class="form-control" style="width:80px;"></td>
            <td><input type="text" name="price[1][153][8]" value="0.00" class="form-control" style="width:80px;"></td>
            <td><input type="text" name="price[1][153][9]" value="0.00" class="form-control" style="width:80px;"></td>
            <td><input type="text" name="price[1][153][10]" value="0.00" class="form-control" style="width:80px;"></td>
            <td><input type="text" name="price[1][153][12]" value="0.00" class="form-control" style="width:80px;"></td>
        </tr>


        <tr bgcolor="#ffffff" style="text-align:center;">
            <td rowspan="2" bgcolor="#efefef"><b>USD</b></td>
            <td>Setup</td>
            <td><input type="text" name="price[3][153][1]" value="0.00" class="form-control" style="width:80px;"></td>
            <td><input type="text" name="price[3][153][2]" value="0.00" class="form-control" style="width:80px;"></td>
            <td><input type="text" name="price[3][153][3]" value="0.00" class="form-control" style="width:80px;"></td>
            <td><input type="text" name="price[3][153][4]" value="0.00" class="form-control" style="width:80px;"></td>
            <td><input type="text" name="price[3][153][5]" value="0.00" class="form-control" style="width:80px;"></td>
            <td><input type="text" name="price[3][153][11]" value="0.00" class="form-control" style="width:80px;"></td>
        </tr>
        <tr bgcolor="#ffffff" style="text-align:center;">
            <td>Pricing</td>
            <td><input type="text" name="price[3][153][6]" value="0.00" class="form-control" style="width:80px;"></td>
            <td><input type="text" name="price[3][153][7]" value="0.00" class="form-control" style="width:80px;"></td>
            <td><input type="text" name="price[3][153][8]" value="0.00" class="form-control" style="width:80px;"></td>
            <td><input type="text" name="price[3][153][9]" value="0.00" class="form-control" style="width:80px;"></td>
            <td><input type="text" name="price[3][153][10]" value="0.00" class="form-control" style="width:80px;"></td>
            <td><input type="text" name="price[3][153][12]" value="0.00" class="form-control" style="width:80px;"></td>
        </tr>
        <tr bgcolor="#ffffff" style="text-align:center;">
            <td rowspan="2" bgcolor="#efefef"><b>USDWIRE</b></td>
            <td>Setup</td>
            <td><input type="text" name="price[5][153][1]" value="0.00" class="form-control" style="width:80px;"></td>
            <td><input type="text" name="price[5][153][2]" value="0.00" class="form-control" style="width:80px;"></td>
            <td><input type="text" name="price[5][153][3]" value="0.00" class="form-control" style="width:80px;"></td>
            <td><input type="text" name="price[5][153][4]" value="0.00" class="form-control" style="width:80px;"></td>
            <td><input type="text" name="price[5][153][5]" value="0.00" class="form-control" style="width:80px;"></td>
            <td><input type="text" name="price[5][153][11]" value="0.00" class="form-control" style="width:80px;"></td>
        </tr>
        <tr bgcolor="#ffffff" style="text-align:center;">
            <td>Pricing</td>
            <td><input type="text" name="price[5][153][6]" value="0.00" class="form-control" style="width:80px;"></td>
            <td><input type="text" name="price[5][153][7]" value="0.00" class="form-control" style="width:80px;"></td>
            <td><input type="text" name="price[5][153][8]" value="0.00" class="form-control" style="width:80px;"></td>
            <td><input type="text" name="price[5][153][9]" value="0.00" class="form-control" style="width:80px;"></td>
            <td><input type="text" name="price[5][153][10]" value="0.00" class="form-control" style="width:80px;"></td>
            <td><input type="text" name="price[5][153][12]" value="0.00" class="form-control" style="width:80px;"></td>
        </tr>
        <tr bgcolor="#efefef">
            <td colspan="9"><b>Add Option:</b> <input type="text" name="addoptionname" class="form-control" style="display:inline-block;width:60%;"></td>
            <td><input type="text" name="addsortorder" value="0" class="form-control" style="width:60px;"></td>
            <td><input type="checkbox" name="addhidden" value="1"></td>
        </tr>
    </tbody>
</table>

    -->