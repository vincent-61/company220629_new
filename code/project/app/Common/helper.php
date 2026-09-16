<?php

/**
 * 通过加盐方式生成加密密码
 *
 * @param $password
 * @param $salt
 *
 * @return string
 *
 * @author VincentZheng <1092161320@qq.com> 2021-06-06
 */
function makePassword($password, $salt): string
{
    return sha1(strtoupper(md5($password . $salt)));
}

/**
 * 检测密码
 *
 * @param $inputPassword
 * @param $dbPassword
 * @param $dbSalt
 *
 * @return bool
 *
 * @author VincentZheng <1092161320@qq.com> 2021-06-06
 */
function checkPassword($inputPassword, $dbPassword, $dbSalt): bool
{
    return (makePassword($inputPassword, $dbSalt) == $dbPassword);
}

/**
 * 获取随机字符串
 *
 * @param $len
 * @param null $chars
 *
 * @return string
 *
 * @author VincentZheng <1092161320@qq.com> 2021-06-15
 */
function getRandomString($len, $chars=null): string
{
    if (is_null($chars)) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    }
    mt_srand(10000000*(double)microtime());
    for ($i = 0, $str = '', $lc = strlen($chars)-1; $i < $len; $i++) {
        $str .= $chars[mt_rand(0, $lc)];
    }
    return $str;
}

/**
 * 获取文件大小
 *
 * @param $num
 * @param $decimal
 *
 * @return string
 *
 * @author VincentZheng <1092161320@qq.com> 2021-06-23
 */
function getFileSize($num, $decimal) {
    $p = 0;
    $format = 'bytes';
    if( $num > 0 && $num < 1024 ) {
        $p = 0;
        return number_format($num) . ' ' . $format;
    }
    if( $num >= 1024 && $num < pow(1024, 2) ){
        $p = 1;
        $format = 'kb';
    }
    if ( $num >= pow(1024, 2) && $num < pow(1024, 3) ) {
        $p = 2;
        $format = 'mb';
    }
    if ( $num >= pow(1024, 3) && $num < pow(1024, 4) ) {
        $p = 3;
        $format = 'gb';
    }
    if ( $num >= pow(1024, 4) && $num < pow(1024, 5) ) {
        $p = 3;
        $format = 'tb';
    }
    $num /= pow(1024, $p);
    return number_format($num, $decimal) . ' ' . $format;
}

/**
 * 获取积分编号
 *
 * @param string $lastPointsNo
 *
 * @return string
 *
 * @author VincentZheng <1092161320@qq.com> 2021-11-26
 */
function getPointsNo($lastPointsNo = '')
{
    if (!empty($lastPointsNo)) {
        $lastNo = preg_replace("/^0+/", '', substr($lastPointsNo, 10));
        $nowNo = str_pad($lastNo + 1,5,"0",STR_PAD_LEFT);
    } else {
        $nowNo = '00001';
    }
    return 'PN' . date('Ymd', time()) . $nowNo;
}

/**
 * 获取申请编号
 *
 * @param string $lastApplyNo
 *
 * @return string
 *
 * @author VincentZheng <1092161320@qq.com> 2021-11-26
 */
function getApplyNo($lastApplyNo = '')
{
    if (!empty($lastApplyNo)) {
        $lastNo = preg_replace("/^0+/", '', substr($lastApplyNo, 10));
        $nowNo = str_pad($lastNo + 1,5,"0",STR_PAD_LEFT);
    } else {
        $nowNo = '00001';
    }
    return 'AN' . date('Ymd', time()) . $nowNo;
}

/**
 * 获取采购单编号
 *
 * @param string $lastPurchaseNo
 *
 * @return string
 *
 * @author VincentZheng <1092161320@qq.com> 2021-06-29
 */
function getPurchaseNo($lastPurchaseNo = '')
{
    if (!empty($lastPurchaseNo)) {
        $lastNo = preg_replace("/^0+/", '', substr($lastPurchaseNo, 10));
        $nowNo = str_pad($lastNo + 1,4,"0",STR_PAD_LEFT);
    } else {
        $nowNo = '0001';
    }
    return 'PO' . date('Ymd', time()) . $nowNo;
}

/**
 * 将数值金额转换为中文大写金额
 * @param $amount float 金额(支持到分)
 * @param $type   int   补整类型,0:到角补整;1:到元补整
 * @return mixed 中文大写金额
 */
function convertAmountToCn($amount, $type = 0) {
    // 判断输出的金额是否为数字或数字字符串
    if(!is_numeric($amount)){
        return "要转换的金额只能为数字!";
    }

    // 金额为0,则直接输出"零元整"
    if($amount == 0) {
        return "零元整";
    }

    // 金额不能为负数
    if($amount < 0) {
        return "要转换的金额不能为负数!";
    }

    // 金额不能超过万亿,即12位
    if(strlen($amount) > 12) {
        return "要转换的金额不能为万亿及更高金额!";
    }

    // 预定义中文转换的数组
    $digital = array('零', '壹', '贰', '叁', '肆', '伍', '陆', '柒', '捌', '玖');
    // 预定义单位转换的数组
    $position = array('仟', '佰', '拾', '亿', '仟', '佰', '拾', '万', '仟', '佰', '拾', '元');

    // 将金额的数值字符串拆分成数组
    $amountArr = explode('.', $amount);

    // 将整数位的数值字符串拆分成数组
    $integerArr = str_split($amountArr[0], 1);

    // 将整数部分替换成大写汉字
    $result = '';
    $integerArrLength = count($integerArr);     // 整数位数组的长度
    $positionLength = count($position);         // 单位数组的长度
    for($i = 0; $i < $integerArrLength; $i++) {
        // 如果数值不为0,则正常转换
        if($integerArr[$i] != 0){
            $result = $result . $digital[$integerArr[$i]] . $position[$positionLength - $integerArrLength + $i];
        }else{
            // 如果数值为0, 且单位是亿,万,元这三个的时候,则直接显示单位
            if(($positionLength - $integerArrLength + $i + 1)%4 == 0){
                $result = $result . $position[$positionLength - $integerArrLength + $i];
            }
        }
    }

    // 如果小数位也要转换
    if($type == 0 && !empty($amountArr[1])) {
        // 将小数位的数值字符串拆分成数组
        $decimalArr = str_split($amountArr[1], 1);

        // 将角替换成大写汉字. 如果为0,则不替换
        if(!empty($decimalArr[0]) && $decimalArr[0] != 0){
            $result = $result . $digital[$decimalArr[0]] . '角';
        }
        // 将分替换成大写汉字. 如果为0,则不替换
        if(!empty($decimalArr[1]) && $decimalArr[1] != 0){
            $result = $result . $digital[$decimalArr[1]] . '分';
        } else {
            $result = $result . '整';
        }
    }else{
        $result = $result . '整';
    }
    return $result;
}

function blurStr($str = '')
{
    // 邮件的话，截取前面部分
    $strEnd = '';
    if (strpos($str, '@') !== false) {
        $strArr = explode('@', $str);
        $str = $strArr[0];
        $strEnd = $strArr[1];
    }

    // 模糊
    $len = strlen($str);
    $pos = $len / 4;
    $newStrArr = [];
    for ($i = 0; $i < $len; $i++) {
        if ($i > $pos && $i < ($pos * 3)) {
            $newStrArr[] = '*';
        } else {
            $newStrArr[] = $str[$i];
        }
    }

    // 组装
    $str = implode('', $newStrArr);

    return $str . $strEnd;
}

function removeHtml($content, $len)
{
    $handle1  = htmlspecialchars_decode($content);  //把一些预定义的 HTML 实体转换为字符
    $handle2  = str_replace("&nbsp;", "", $handle1);//将空格替换成空
    $contents = strip_tags($handle2);               //函数剥去字符串中的 HTML、XML 以及 PHP 的标签,获取纯文本内容

    return mb_substr($contents, 0, $len, "utf-8");        //返回字符串中的前100字符串长度的字符
}
