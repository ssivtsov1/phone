<?php
function debug($var)
{
    echo '<pre>';
    print_r($var);
    echo '</pre>';
}

// Нормализация № телефона
function tel_normal($tel){
    $len = strlen($tel);
    $rez = '';
    $pos = strpos($tel,'(');
    if($pos>0) $len = 0;
    switch ($len){
        case 10:
            $op = substr($tel,0,3);
            $rez.=$op.' ';
            $op = substr($tel,3,3);
            $rez.=$op.'-';
            $add = substr($tel,6,2);
            $rez.=$add.'-';
            $add = substr($tel,8);
            $rez.=$add;
            return $rez;
        case 7:
            $op = substr($tel,0,3);
            $rez.=$op.'-';
            $add = substr($tel,3,2);
            $rez.=$add.'-';
            $add = substr($tel,5);
            $rez.=$add;
            return $rez;
        case 6:
            $op = substr($tel,0,2);
            $rez.=$op.'-';
            $add = substr($tel,2,2);
            $rez.=$add.'-';
            $add = substr($tel,4);
            $rez.=$add;
            return $rez;
        case 5:
            $op = substr($tel,0,1);
            $rez.=$op.'-';
            $add = substr($tel,1,2);
            $rez.=$add.'-';
            $add = substr($tel,3);
            $rez.=$add;
            return $rez;
        default:  
            return $tel;
    }       
    
    
}
// Возвращает строку только с цифрами
function only_digit($data){
    $rez='';
    $len = strlen($data);
    for($i=0;$i<$len;$i++){
        $c=substr($data,$i,1);
        if(is_numeric($c)) $rez.=$c;
    }
    return $rez;
}

// Возвращает строку только с цифрами и запятой
function only_digit1($data){
    $rez='';
    $len = strlen($data);
    for($i=0;$i<$len;$i++){
        $c=substr($data,$i,1);
        if(is_numeric($c) || $c==',') $rez.=$c;
    }
    return $rez;
}

// Изменение формата даты
function changeDateFormat($sourceDate, $newFormat) {
    $r = date($newFormat, strtotime($sourceDate));
    return $r;
}    
    
// Преобразование в число прописью
function num2text_ua($num) {
        $num = trim(preg_replace('~s+~s', '', $num)); // отсекаем пробелы
        if (preg_match("/, /", $num)) {
            $num = preg_replace("/, /", ".", $num);
            
        } // преобразует запятую
        
        if (is_numeric($num)) {
            
            //$num = round($num, 2); // Округляем до сотых (копеек)
            
            $num_arr = explode(".", $num);
            $amount = $num_arr[0]; // переназначаем для удобства, $amount - сумма без копеек
            if (strlen($amount) <= 3) {
                $res = implode(" ", Triada($amount)) . Currency($amount);
            } else {
                $amount1 = $amount;
                while (strlen($amount1) >= 3) {
                    $temp_arr[] = substr($amount1, -3); // засовываем в массив по 3
                    $amount1 = substr($amount1, 0, -3); // уменьшаем массив на 3 с конца
                }
                if ($amount1 != '') {
                    $temp_arr[] = $amount1;
                } // добавляем то, что не добавилось по 3
                $i = 0;
                foreach ($temp_arr as $temp_var) { // переводим числа в буквы по 3 в массиве
                    $i++;
                    if ($i == 3 || $i == 4) { // миллионы и миллиарды мужского рода, а больше миллирда вам все равно не заплатят
                        if ($temp_var == '000') {

                            $temp_res[] = '';
                        } else {
                            $temp_res[] = implode(" ", Triada($temp_var, 1)) . GetNum($i, $temp_var);
                        } # if
                    } else {
                        if ($temp_var == '000') {
                            $temp_res[] = '';
                        } else {
                            $temp_res[] = implode(" ", Triada($temp_var)) . GetNum($i, $temp_var);
                        } # if
                    } # else
                } # foreach
                $temp_res = array_reverse($temp_res); // разворачиваем массив
                $res = implode(" ", $temp_res) . Currency($amount);
            }
            if (!isset($num_arr[1]) || $num_arr[1] == '') {
                $num_arr[1] = '00';
            }
            return $res . ', ' . $num_arr[1] . ' коп.';
        } # if
    }

    function Triada($amount, $case = null) {
        global $_1_2, $_1_19, $des, $hang; // объявляем массив переменных
        $count = strlen($amount);
        for ($i = 0; $i < $count; $i++) {
            $triada[] = substr($amount, $i, 1);
        }
        $triada = array_reverse($triada); // разворачиваем массив для операций
        if (isset($triada[1]) && $triada[1] == 1) { // строго для 10-19
            $triada[0] = $triada[1] . $triada[0]; // Объединяем в единицы
            $triada[1] = ''; // убиваем десятки
            $triada[0] = $_1_19[$triada[0]]; // присваиваем
        } else { // а дальше по обычной схеме
            if (isset($case) && ($triada[0] == 1 || $triada[0] == 2)) { // если требуется м.р.
                $triada[0] = $_1_2[$triada[0]]; // единицы, массив мужского рода
            } else {
                if ($triada[0] != 0) {
                    $triada[0] = $_1_19[$triada[0]];
                } else {
                    $triada[0] = '';
                } // единицы
            } # if
            if (isset($triada[1]) && $triada[1] != 0) {
                $triada[1] = $des[$triada[1]];
            } else {
                $triada[1] = '';
            } // десятки
        }
        if (isset($triada[2]) && $triada[2] != 0) {
            $triada[2] = $hang[$triada[2]];
        } else {
            $triada[2] = '';
        } // сотни
        $triada = array_reverse($triada); // разворачиваем массив для вывода
        foreach ($triada as $triada_) { // вычищаем массив от пустых значений
            if ($triada_ != '') {
                $triada1[] = $triada_;
            }
        } # foreach
        return $triada1;
    }

    function Currency($amount) {
        global $namecurr; // объявляем масиив переменных
        $last2 = substr($amount, -2); // последние 2 цифры
        $last1 = substr($amount, -1); // последняя 1 цифра
        $last3 = substr($amount, -3); //последние 3 цифры
        if ((strlen($amount) != 1 && substr($last2, 0, 1) == 1) || $last1 >= 5 || $last3 == '000') {
            $curr = $namecurr[3];
        } // от 10 до 19
        else if ($last1 == 1) {
            $curr = $namecurr[1];
        } // для 1-цы
        else {
            $curr = $namecurr[2];
        } // все остальные 2, 3, 4
        return ' ' . $curr;
    }

    function GetNum($level, $amount) {
        global $nametho, $namemil, $namemrd; // объявляем массив переменных
        if ($level == 1) {
            $num_arr = null;
        } else if ($level == 2) {
            $num_arr = $nametho;
        } else if ($level == 3) {
            $num_arr = $namemil;
        } else if ($level == 4) {
            $num_arr = $namemrd;
        } else {
            $num_arr = null;
        }
        if (isset($num_arr)) {
            $last2 = substr($amount, -2);
            $last1 = substr($amount, -1);
            if ((strlen($amount) != 1 && substr($last2, 0, 1) == 1) || $last1 >= 5) {
                $res_num = $num_arr[3];
            } // 10-19
            else if ($last1 == 1) {
                $res_num = $num_arr[1];
            } // для 1-цы
            else {
                $res_num = $num_arr[2];
            } // все остальные 2, 3, 4
            return ' ' . $res_num;
        } # if
    }

    $_1_2[1] = "один";
    $_1_2[2] = "два";

    $_1_19[1] = "одна";
    $_1_19[2] = "дві";
    $_1_19[3] = "три";
    $_1_19[4] = "чотири";
    $_1_19[5] = "п'ять";
    $_1_19[6] = "шість";
    $_1_19[7] = "сім";
    $_1_19[8] = "вісім";
    $_1_19[9] = "дев'ять";
    $_1_19[10] = "десять";

    $_1_19[11] = "одинадцять";
    $_1_19[12] = "дванадцять";
    $_1_19[13] = "тринадцять";
    $_1_19[14] = "чотирнадцять";
    $_1_19[15] = "п'ятнадцять";
    $_1_19[16] = "шістнадцять";
    $_1_19[17] = "сімнадцять";
    $_1_19[18] = "вісімнадцять";
    $_1_19[19] = "дев'ятнадцять";


    $des[2] = "двадцять";
    $des[3] = "тридцять";
    $des[4] = "сорок";
    $des[5] = "п'ятдесят";
    $des[6] = "шістьдесят";
    $des[7] = "сімдесят";
    $des[8] = "вісімдесят";
    $des[9] = "дев'яносто";

    $hang[1] = "сто";
    $hang[2] = "двісті";
    $hang[3] = "триста";
    $hang[4] = "чотириста";
    $hang[5] = "п'ятсот";
    $hang[6] = "шістьсот";
    $hang[7] = "сімсот";
    $hang[8] = "вісімсот";
    $hang[9] = "дев'ятьсот";

    $namecurr[1] = "гривня"; // 1
    $namecurr[2] = "гривні"; // 2, 3, 4
    $namecurr[3] = "гривень"; // >4

    $nametho[1] = "тисяча"; // 1
    $nametho[2] = "тисячі"; // 2, 3, 4
    $nametho[3] = "тисяч"; // >4

    $namemil[1] = "мільйон"; // 1
    $namemil[2] = "мільйона"; // 2, 3, 4
    $namemil[3] = "мільйонів"; // >4

    $namemrd[1] = "мільярд"; // 1
    $namemrd[2] = "мільярда"; // 2, 3, 4
    $namemrd[3] = "мільярдів"; // >4

    // Преобразование строки - в первый заглавный символ (для русских символов)
    // $code - кодировка (обычно UTF-8)
    function mb_ucfirst($str,$code) {
    $fc = mb_strtoupper(mb_substr($str, 0, 1,$code),$code);
    return $fc.mb_substr($str,1,256,$code);
}

function recode_c($s) {
$s1='';
$y=strlen($s);
for($i=0;$i<$y;$i++){
$c=substr($s,$i,1);
switch($c) {
    case 'q':
        $nc = 'й';
        break;
    case 'w':
        $nc = 'ц';
        break;
    case 'e':
        $nc = 'у';
        break;
    case 'r':
        $nc = 'к';
        break;
    case 't':
        $nc = 'е';
        break;

    case 'y':
        $nc = 'н';
        break;
    case 'u':
        $nc = 'г';
        break;
    case 'i':
        $nc = 'ш';
        break;
    case 'o':
        $nc = 'щ';
        break;
    case 'p':
        $nc = 'з';
        break;

    case '[':
        $nc = 'х';
        break;
    case ']':
        $nc = 'ъ';
        break;
    case 'a':
        $nc = 'ф';
        break;
    case 's':
        $nc = 'і';
        break;
    case 's':
        $nc = 'ы';
        break;
    case 'd':
        $nc = 'в';
        break;

    case 'f':
        $nc = 'а';
        break;
    case 'g':
        $nc = 'п';
        break;
    case 'h':
        $nc = 'р';
        break;
    case 'j':
        $nc = 'о';
        break;
    case 'k':
        $nc = 'л';
        break;

    case 'l':
        $nc = 'д';
        break;
    case ';':
        $nc = 'ж';
        break;
    case "'":
        $nc = 'э';
        break;
    case 'z':
        $nc = 'я';
        break;
    case 'x':
        $nc = 'ч';
        break;

    case 'c':
        $nc = 'с';
        break;
    case 'v':
        $nc = 'м';
        break;
    case "b":
        $nc = 'и';
        break;
    case 'n':
        $nc = 'т';
        break;
    case 'm':
        $nc = 'ь';
        break;
    case ',':
        $nc = 'б';

        break;
    case '.':
        $nc = 'ю';
        break;
    case '>':
        $nc = 'ю';
        break;
    case '<':
        $nc = 'б';
        break;
    case ':':
        $nc = 'ж';
        break;
    case '{':
        $nc = 'х';
        break;
}
$s1.=$nc;
}
return $s1;
    }
?>
