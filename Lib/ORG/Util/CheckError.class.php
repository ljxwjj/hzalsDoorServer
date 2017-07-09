<?php
/*----------------------------------------------------------------------
 * [名称] SC_CheckError
 * [概要] 错误检查
 *----------------------------------------------------------------------
 */
namespace Lib\ORG\Util;
class CheckError {
    var $arrErr;
    var $arrParam;

    function checkError($array = "") {
    	//加载语言包，在启用语言包的情况下，CheckError语言包必须创建
//    	if(ZYD::getAppInf('multiLanguageSupport')){
//	    	load_language('CheckError');
//    	}
        if($array != "") {
            $this->arrParam = $array;
        } else {
            $this->arrParam = $_POST;
        }

    }

    function doFunc($value, $arrFunc) {
        foreach ( $arrFunc as $key ) {
            $this->$key($value);
        }
    }

    /* HTMLのタグをチェックする */
    // value[0] = 項目名 value[1] = 判定対象 value[2] = 許可するタグが格納された配列
    function HTML_TAG_CHECK($value) {
        if(isset($this->arrErr[$value[1]])) {
            return;
        }
        $this->createParam($value);
        // 含まれているタグを抽出する
        preg_match_all("/<([\/]?[a-z]+)/", $this->arrParam[$value[1]], $arrTag);

        foreach($arrTag[1] as $val) {
            $find = false;

            foreach($value[2] as $tag) {
                if(eregi("^" . $tag . "$", $val)) {
                    $find = true;
                } else {
                }
            }

            if(!$find) {
                $this->arrErr[$value[1]] = 
//                //ZYD::getAppInf('multiLanguageSupport') ?  
//                _t('%0%[%1%] is not allowed to label',array($value[0],strtoupper($val))) : 
                $value[0] . "[" . strtoupper($val) . "]是不准许的标签";
                return;
            }
        }
    }

    /*　必须输入判定　*/
    // value[0] = 項目名 value[1] = 判定対象
    function EXIST_CHECK( $value ) {			// 受け取りがない場合エラーを返す
        if(isset($this->arrErr[$value[1]])) {
            return;
        }
        $this->createParam($value);
        if( strlen($this->arrParam[$value[1]]) == 0 ){
            $this->arrErr[$value[1]] = 
            //ZYD::getAppInf('multiLanguageSupport') ?  
            //_t('Please enter %0%',array($value[0])) : 
            "请输入".$value[0];            
        }
    }

    /*　必须输入判定（反向）　*/
    // value[0] = 判定対象 value[1] = 項目名
    function EXIST_CHECK_REVERSE( $value ) {			// 受け取りがない場合エラーを返す
        if(isset($this->arrErr[$value[0]])) {
            return;
        }
        $this->createParam($value);
        if( strlen($this->arrParam[$value[0]]) == 0 ){
            $this->arrErr[$value[0]] = 
            //ZYD::getAppInf('multiLanguageSupport') ?  
            //_t('Please enter %0%',array($value[0])) : 
            "请输入".$value[0];             
        }
    }

    /*　空格、换行、制表符判定　*/
    // value[0] = 項目名 value[1] = 判定対象
    function SPTAB_CHECK( $value ) {			// 受け取りがない場合エラーを返す
        if(isset($this->arrErr[$value[1]])) {
            return;
        }
        $this->createParam($value);
        if(strlen($this->arrParam[$value[1]]) != 0 && ereg("^[ 　\t\r\n]+$", $this->arrParam[$value[1]])){
            $this->arrErr[$value[1]] = $value[0] . "不能含有空格等特殊字符";
        }
    }

    /*　空格、换行、制表符判定　*/
    // value[0] = 項目名 value[1] = 判定対象
    function NO_SPTAB( $value ) {			// 受け取りがない場合エラーを返す
        if(isset($this->arrErr[$value[1]])) {
            return;
        }
        $this->createParam($value);
        if(strlen($this->arrParam[$value[1]]) != 0 && mb_ereg("[　 \t\r\n]+", $this->arrParam[$value[1]])){
            $this->arrErr[$value[1]] = $value[0] . "不能含有空格等特殊字符";
        }
    }

    /* 0开始判定 */
    function ZERO_START($value) {
        if(isset($this->arrErr[$value[1]])) {
            return;
        }
        $this->createParam($value);
        if(strlen($this->arrParam[$value[1]]) != 0 && ereg("^[0]+[0-9]+$", $this->arrParam[$value[1]])){
            $this->arrErr[$value[1]] = $value[0] . "不能是从0开始的数字号码";
        }
    }

    /*　必须选择判定　*/
    // value[0] = 項目名 value[1] = 判定対象
    function SELECT_CHECK( $value ) {			// プルダウンなどで選択されていない場合エラーを返す
        if(isset($this->arrErr[$value[1]])) {
            return;
        }
        $this->createParam($value);
        if( strlen($this->arrParam[$value[1]]) == 0 ){
            $this->arrErr[$value[1]] = $value[0] . "必须选择一项";
        }
    }

    /*　一致性判定　*/
    // value[0] = 項目名1 value[1] = 項目名2 value[2] = 判定対象文字列1  value[3] = 判定対象文字列2
    function EQUAL_CHECK( $value ) {		// 入力が指定文字数以上ならエラーを返す
        if(isset($this->arrErr[$value[2]]) || isset($this->arrErr[$value[3]])) {
            return;
        }
        $this->createParam($value);
        // 文字数の取得
        if( $this->arrParam[$value[2]] != $this->arrParam[$value[3]]) {
            $this->arrErr[$value[2]] = $value[0] . "和" . $value[1] . "不一致，请重新输入";
        }
    }

    /*　不一致性判定　*/
    // value[0] = 項目名1 value[1] = 項目名2 value[2] = 判定対象文字列1  value[3] = 判定対象文字列2
    function DIFFERENT_CHECK( $value ) {		// 入力が指定文字数以上ならエラーを返す
        if(isset($this->arrErr[$value[2]]) || isset($this->arrErr[$value[3]])) {
            return;
        }
        $this->createParam($value);
        // 文字数の取得
        if( $this->arrParam[$value[2]] == $this->arrParam[$value[3]]) {
            $this->arrErr[$value[2]] = $value[0] . "和" . $value[1] . "不可以是一样的";
        }
    }

    /*　值大小比较 value[2] < value[3]才可通过　*/
    // value[0] = 項目名1 value[1] = 項目名2 value[2] = 判定対象文字列1  value[3] = 判定対象文字列2
    function GREATER_CHECK($value) {		// 入力が指定文字数以上ならエラーを返す
        if(isset($this->arrErr[$value[2]]) || isset($this->arrErr[$value[3]])) {
            return;
        }
        $this->createParam($value);
        // 文字数の取得
        if($this->arrParam[$value[2]] != "" && $this->arrParam[$value[3]] != "" && ($this->arrParam[$value[2]] > $this->arrParam[$value[3]])) {
            $this->arrErr[$value[2]] = $value[0] . "不能大于" . $value[1];
        }
    }


    /*　最大文字数限定　*/
    // value[0] = 項目名 value[1] = 判定対象文字列  value[2] = 最大文字数(半角も全角も1文字として数える)
    function MAX_LENGTH_CHECK( $value ) {		// 中英文混合
        if(isset($this->arrErr[$value[1]])) {
            return;
        }
        $this->createParam($value);
        // 文字数の取得
        if( mb_strlen($this->arrParam[$value[1]],C('RESPONSE_CHARSET')) > $value[2] ) {
            $this->arrErr[$value[1]] = $value[0] . "不能超过" . $value[2] . "字";
        }
    }

    /*　最小文字数限定　*/
    // value[0] = 項目名 value[1] = 判定対象文字列 value[2] = 最小文字数(半角も全角も1文字として数える)
    function MIN_LENGTH_CHECK( $value ) {		// 中英文混合
        if(isset($this->arrErr[$value[1]])) {
            return;
        }
        $this->createParam($value);
        // 文字数の取得
        if( mb_strlen($this->arrParam[$value[1]],C('RESPONSE_CHARSET')) < $value[2] ) {
            $this->arrErr[$value[1]] = $value[0] . "不能少于" . $value[2] . "字";
        }
    }

    /*　最大文字数限定　*/
    // value[0] = 項目名 value[1] = 判定対象文字列  value[2] = 最大数
    function MAX_CHECK( $value ) {		// 入力が最大数以上ならエラーを返す
        if(isset($this->arrErr[$value[1]])) {
            return;
        }
        $this->createParam($value);
        // 文字数の取得
        if($this->arrParam[$value[1]] > $value[2] ) {
            $this->arrErr[$value[1]] = $value[0] . "不能超过" . $value[2];
        }
    }

    /*　最小数値制限の判定　*/
    // value[0] = 項目名 value[1] = 判定対象文字列  value[2] = 最小数
    function MIN_CHECK( $value ) {		// 入力が最小数未満ならエラーを返す
        if(isset($this->arrErr[$value[1]])) {
            return;
        }
        $this->createParam($value);
        if($this->arrParam[$value[1]] < $value[2] ) {
            $this->arrErr[$value[1]] = $value[0] . "不能小于" . $value[2];
        }
    }


    /*　数字判定　*/
    // value[0] = 項目名 value[1] = 判定対象文字列
    function NUM_CHECK( $value ) {				// 入力文字が数字以外ならエラーを返す
        if(isset($this->arrErr[$value[1]])) {
            return;
        }
        $this->createParam($value);
        if( strlen($this->arrParam[$value[1]]) > 0 && !EregI("^[[:digit:]]+$", $this->arrParam[$value[1]])) {
            $this->arrErr[$value[1]] = $value[0] . "必须是数字";
        }
    }

        /*　包含小数点的数字判定　*/
    // value[0] = 項目名 value[1] = 判定対象文字列
    function NUM_POINT_CHECK( $value ) {				// 入力文字が数字以外ならエラーを返す
        if(isset($this->arrErr[$value[1]])) {
            return;
        }
        $this->createParam($value);
        if( strlen($this->arrParam[$value[1]]) > 0 && !EregI("^[[:digit:]]+([\.][[:digit:]]+)?$", $this->arrParam[$value[1]])) {
            $this->arrErr[$value[1]] = $value[0] . "必须是数字";
        }
    }

    function ALPHA_CHECK($value) {
        if(isset($this->arrErr[$value[1]])) {
            return;
        }
        $this->createParam($value);
        if( strlen($this->arrParam[$value[1]]) > 0 && !EregI("^[[:alpha:]]+$", $this->arrParam[$value[1]])) {
            $this->arrErr[$value[1]] = $value[0] . "必须是英文或数字";
        }
    }

    /* 電話番号の判定 （数字チェックと文字数チェックを実施する。)
        value[0] : 項目名
        value[1] : 電番1項目目
        value[2] : 電番2項目目
        value[3] : 電番3項目目
        value[4] : 文字数制限
    */
    function TEL_CHECK($value) {
        if(isset($this->arrErr[$value[1]])) {
            return;
        }
		//print_r($value);
        $this->createParam($value);
        $cnt = 0;

        for($i = 1; $i <= 3; $i++) {
            if(strlen($this->arrParam[$value[$i]]) > 0) {
                $cnt++;
            }
        }

        // すべての項目が満たされていない場合を判定(一部だけ入力されている状態)
        if($cnt > 0 && $cnt < 3) {
            $this->arrErr[$value[1]] .= $value[0] . "必须输入";
        }

        $total_count = 0;
        for($i = 1; $i <= 3; $i++) {
            if(strlen($this->arrParam[$value[$i]]) > 0 && strlen($this->arrParam[$value[$i]]) > $value[4]) {
                $this->arrErr[$value[$i]] .= $value[0] . $i . "必须在" . $value[4] . "字符以内";
            } else if (strlen($this->arrParam[$value[$i]]) > 0 && !EregI("^[[:digit:]]+$", $this->arrParam[$value[$i]])) {
                $this->arrErr[$value[$i]] .= "※ " . $value[0] . $i . "必须是数字";
            }
            $total_count += strlen($this->arrParam[$value[$i]]);
        }

        // 合計値チェック
        if ($total_count > TEL_LEN) {
            $this->arrErr[$value[3]] .= $value[0] . "必须在" . TEL_LEN . "字以内";
        }
    }
	
	
	function CN_MOBILE_CHECK($value) {
		$phonepattern = '/^(13[0-9]|15[^4\D]|18[05-9])\d{8}$/';
        if(isset($this->arrErr[$value[1]])) {
            return;
        }
		
        $this->createParam($value);
		if(!preg_match($phonepattern,$this->arrParam[$value[1]])){
			
			$this->arrErr[$value[1]] .=  $value[0] .  "请输入正确的手机号码";
		}

    }




    /* 関連項目が完全に満たされているか判定
        value[0]		: 項目名
        value[1]		: 判定対象要素名
    */
    function FULL_EXIST_CHECK($value) {
        $max = count($value);
        $this->createParam($value);
		
        // 既に該当項目にエラーがある場合は、判定しない。
        for($i = 1; $i < $max; $i++) {
            if(isset($this->arrErr[$value[$i]])) {
                return;
            }
        }

        $blank = false;

        // すべての項目がブランクでないか、すべての項目が入力されていない場合はエラーとする。
        for($i = 1; $i < $max; $i++) {
            if(strlen($this->arrParam[$value[$i]]) <= 0) {
                $blank = true;
            }
        }

        if($blank) {
            $this->arrErr[$value[1]] = $value[0] . "必须输入";
        }
    }

    /* 関連項目がすべて満たされているか判定
        value[0]		: 項目名
        value[1]		: 判定対象要素名
    */
    function ALL_EXIST_CHECK($value) {
        $max = count($value);

        // 既に該当項目にエラーがある場合は、判定しない。
        for($i = 1; $i < $max; $i++) {
            if(isset($this->arrErr[$value[$i]])) {
                return;
            }
        }

        $blank = false;
        $input = false;

        // すべての項目がブランクでないか、すべての項目が入力されていない場合はエラーとする。
        for($i = 1; $i < $max; $i++) {
            if(strlen($this->arrParam[$value[$i]]) <= 0) {
                $blank = true;
            } else {
                $input = true;
            }
        }

        if($blank && $input) {
            $this->arrErr[$value[1]] = $value[0] . "是必须输入的项目";
        }
    }

        /* 関連項目がどれか一つ満たされているか判定
        value[0]		: 項目名
        value[1]		: 判定対象要素名
    */
    function ONE_EXIST_CHECK($value) {
        $max = count($value);
        $this->createParam($value);
        // 既に該当項目にエラーがある場合は、判定しない。
        for($i = 1; $i < $max; $i++) {
            if(isset($this->arrErr[$value[$i]])) {
                return;
            }
        }

        $input = false;

        // すべての項目がブランクでないか、すべての項目が入力されていない場合はエラーとする。
        for($i = 1; $i < $max; $i++) {
            if(strlen($this->arrParam[$value[$i]]) > 0) {
                $input = true;
            }
        }

        if(!$input) {
            $this->arrErr[$value[1]] = $value[0] . "没有输入";
        }
    }

    /* 上位の項目が満たされているか判定
        value[0]		: 項目名
        value[1]		: 判定対象要素名
    */
    function TOP_EXIST_CHECK($value) {
        $max = count($value);
        $this->createParam($value);

        // 既に該当項目にエラーがある場合は、判定しない。
        for($i = 1; $i < $max; $i++) {
            if(isset($this->arrErr[$value[$i]])) {
                return;
            }
        }

        $blank = false;
        $error = false;

        // すべての項目がブランクでないか、すべての項目が入力されていない場合はエラーとする。
        for($i = 1; $i < $max; $i++) {
            if(strlen($this->arrParam[$value[$i]]) <= 0) {
                $blank = true;
            } else {
                if($blank) {
                    $error = true;
                }
            }
        }

        if($error) {
            $this->arrErr[$value[1]] = $value[0] . "应该按项目顺序输入";
        }
    }


    /*　カタカナの判定　*/
    // value[0] = 項目名 value[1] = 判定対象文字列
    function KANA_CHECK( $value ) {				// 入力文字がカナ以外ならエラーを返す
        if(isset($this->arrErr[$value[1]])) {
            return;
        }
        $this->createParam($value);
        if(strlen($this->arrParam[$value[1]]) > 0 && ! mb_ereg("^[ァ-ヶ?-?ー]+$", $this->arrParam[$value[1]])) {
            $this->arrErr[$value[1]] = $value[0] . "必须是日文假名";
        }
    }

    /*　カタカナの判定2（タブ、スペースは許可する）　*/
    // value[0] = 項目名 value[1] = 判定対象文字列
    function KANABLANK_CHECK( $value ) {				// 入力文字がカナ以外ならエラーを返す
        if(isset($this->arrErr[$value[1]])) {
            return;
        }
        $this->createParam($value);
        if(strlen($this->arrParam[$value[1]]) > 0 && ! mb_ereg("^([　 \t\r\n]|[ァ-ヶ]|[ー])+$", $this->arrParam[$value[1]])) {
            $this->arrErr[$value[1]] = $value[0] . "必须是日文假名";
        }
    }

    /*　英数字の判定　*/
    // value[0] = 項目名 value[1] = 判定対象文字列
    function ALNUM_CHECK( $value ) {				// 入力文字が英数字以外ならエラーを返す
        if(isset($this->arrErr[$value[1]])) {
            return;
        }
        $this->createParam($value);
        if( strlen($this->arrParam[$value[1]]) > 0 && ! EregI("^[[:alnum:]]+$", $this->arrParam[$value[1]] ) ) {
            $this->arrErr[$value[1]] = $value[0] . "必须是英文或数字";
        }
    }

    /*　英数記号の判定　*/
    // value[0] = 項目名 value[1] = 判定対象文字列
    function GRAPH_CHECK( $value ) {				// 入力文字が英数記号以外ならエラーを返す
        if(isset($this->arrErr[$value[1]])) {
            return;
        }
        $this->createParam($value);
        if( strlen($this->arrParam[$value[1]]) > 0 && ! EregI("^[[:graph:]]+$", $this->arrParam[$value[1]] ) ) {
            $this->arrErr[$value[1]] = $value[0] . "必须是英文或数字";
        }
    }

    /*　必須選択の判定　*/
    // value[0] = 項目名 value[1] = 判定対象
    function ZERO_CHECK( $value ) {				// 入力値で0が許されない場合エラーを返す
        $this->createParam($value);
        if($this->arrParam[$value[1]] == "0" ){
            $this->arrErr[$value[1]] = $value[0] . "至少选择一项";
        }
    }

    /*　桁数の判定　（最小最大）*/
    // value[0] = 項目名 value[1] = 判定対象文字列 value[2] = 最小桁数 value[3] = 最大桁数
    function NUM_RANGE_CHECK( $value ) {		// 输入文字　→　最小数＜输入数＜最大数  用于英文数字场合
        if(isset($this->arrErr[$value[1]])) {
            return;
        }
        $this->createParam($value);
        $this->arrParam[$value[0]] = mb_convert_kana($this->arrParam[$value[0]], "n");
        $count = strlen($this->arrParam[$value[1]]);
 
        if($count > 0 && ( $value[2] > $this->arrParam[$value[1]] || $value[3] < $this->arrParam[$value[1]] ) ) {
            $this->arrErr[$value[1]] =  "$value[0]应该在$value[2]位$value[3]位之间";
        }
    }
    
    /*　文字数判定　（最小最大）*/
    // value[0] = 項目名 value[1] = 判定対象文字列 value[2] = 最小桁数 value[3] = 最大桁数
    function STRING_RANGE_CHECK( $value ) {		// 输入文字　→　最小数<=输入数<=最大数  中英文混合
        if(isset($this->arrErr[$value[1]])) {
            return;
        }
        $this->createParam($value);
        $count = mb_strlen($this->arrParam[$value[1]],C('RESPONSE_CHARSET'));
        if( ( $count > 0 ) && $value[2] > $count || $value[3] < $count ) {
            $this->arrErr[$value[1]] =  "$value[0]应该在$value[2]到$value[3]个字数之间";
        }
    }    

    /*　桁数の判定　*/
    // value[0] = 項目名 value[1] = 判定対象文字列 value[2] = 桁数
    function NUM_COUNT_CHECK( $value ) {		// 入力文字の桁数判定　→　入力文字列 = 桁数　以外はNGの場合
        if(isset($this->arrErr[$value[1]])) {
            return;
        }
        $this->createParam($value);
        $count = strlen($this->arrParam[$value[1]]);
        if(($count > 0) && $count != $value[2] ) {
            $this->arrErr[$value[1]] =  "$value[0]应该在$value[2]位数以下";
        }
    }

    /*　メールアドレス形式の判定　*/
    // value[0] = 項目名 value[1] = 判定対象メールアドレス
    function EMAIL_CHECK( $value ){				//　メールアドレスを正規表現で判定する
        if(isset($this->arrErr[$value[1]])) {
            return;
        }
        $this->createParam($value);
        if(strlen($this->arrParam[$value[1]]) > 0 && !ereg("^[^@]+@[^.^@]+\..+", $this->arrParam[$value[1]])) {
            $this->arrErr[$value[1]] = $value[0] . "格式不正确";
        }
    }

    /*　メールアドレスに使用できる文字の判定　*/
    //  value[0] = 項目名 value[1] = 判定対象メールアドレス
    function EMAIL_CHAR_CHECK( $value ){				//　メールアドレスに使用する文字を正規表現で判定する
        if(isset($this->arrErr[$value[1]])) {
            return;
        }
        $this->createParam($value);
        if(strlen($this->arrParam[$value[1]]) > 0 && !ereg("^[a-zA-Z0-9_\.@\+\?-]+$",$this->arrParam[$value[1]]) ) {
            $this->arrErr[$value[1]] = $value[0] . "格式不正确";
        }
    }

    /*　URL形式の判定　*/
    //  value[0] = 項目名 value[1] = 判定対象URL
    function URL_CHECK( $value ){				//　URLを正規表現で判定する。デフォルトでhttp://があってもOK
         if(isset($this->arrErr[$value[1]])) {
            return;
        }
        if( strlen($this->arrParam[$value[1]]) > 0 && !ereg( "^https?://+($|[a-zA-Z0-9_~=:&\?\.\/-])+$", $this->arrParam[$value[1]] ) ) {
            $this->arrErr[$value[1]] = $value[0] . "格式不正确";
        }
    }

    /*　拡張子の判定　*/
    // value[0] = 項目名 value[1] = 判定対象 value[2]=array(拡張子)
    function FILE_EXT_CHECK( $value ) {			// 受け取りがない場合エラーを返す
        if(isset($this->arrErr[$value[1]]) || count($value[2]) == 0) {
            return;
        }
        $this->createParam($value);

        if($_FILES[$value[1]]['name'] != "" ) {
            $errFlag = 1;
            $array_ext = explode(".", $_FILES[$value[1]]['name']);
            $ext = $array_ext[ count ( $array_ext ) - 1 ];
            $ext = strtolower($ext);

            $strExt = "";

            foreach ( $value[2] as $checkExt ){
                if ( $ext == $checkExt) {
                    $errFlag = 0;
                }

                if($strExt == "") {
                    $strExt.= $checkExt;
                } else {
                    $strExt.= "?$checkExt";
                }
            }
        }
        if ($errFlag == 1) {
            $this->arrErr[$value[1]] = $value[0] . "只准许上传". $strExt ."文件格式";
        }
    }

    /* ファイルが存在するかチェックする */
    // value[0] = 項目名 value[1] = 判定対象  value[2] = 指定ディレクトリ
    function FIND_FILE( $value ) {			// 受け取りがない場合エラーを返す
        if(isset($this->arrErr[$value[1]])) {
            return;
        }

        $this->createParam($value);
        if($value[2] != "") {
            $dir = $value[2];
        } else {
            $dir = IMAGE_SAVE_DIR;
        }

        $path = $dir . "/" . $this->arrParam[$value[1]];
        $path = ereg_replace("//", "/", $path);

        if($this->arrParam[$value[1]] != "" && !file_exists($path)){
            $this->arrErr[$value[1]] = $path . "找不到";
        }
    }

    /*　ファイルが上げられたか確認　*/
    // value[0] = 項目名 value[1] = 判定対象  value[2] = 指定サイズ（KB)
    function FILE_EXIST_CHECK( $value ) {			// 受け取りがない場合エラーを返す
        if(isset($this->arrErr[$value[1]])) {
            return;
        }
        $this->createParam($value);
        if(!($_FILES[$value[1]]['size'] != "" && $_FILES[$value[1]]['size'] > 0)){
            $this->arrErr[$value[1]] = $value[0] . "不存在";
        }
    }

    /*　ファイルサイズの判定　*/
    // value[0] = 項目名 value[1] = 判定対象  value[2] = 指定サイズ（KB)
    function FILE_SIZE_CHECK( $value ) {			// 受け取りがない場合エラーを返す
        if(isset($this->arrErr[$value[1]])) {
            return;
        }
        $this->createParam($value);
        if( $_FILES[$value[1]]['size'] > $value[2] *  1024 ){
            $byte = "KB";
            if( $value[2] >= 1000 ) {
                $value[2] = $value[2] / 1000;
                $byte = "MB";
            }
            $this->arrErr[$value[1]] = $value[0] . "不能超过" . $value[2] . $byte;
        }
    }

    /*　ファイル名の判定　*/
    // value[0] = 項目名 value[1] = 判定対象文字列
    function FILE_NAME_CHECK( $value ) {				// 入力文字が英数字,"_","-"以外ならエラーを返す
        if(isset($this->arrErr[$value[1]])) {
            return;
        }
        $this->createParam($value);
        if( strlen($_FILES[$value[1]]['name']) > 0 && ! EregI("^[[:alnum:]_\.-]+$", $_FILES[$value[1]]['name']) ) {
            $this->arrErr[$value[1]] = $value[0] . "的文件名不能使用中文";
        }
    }

    /*　ファイル名の判定(アップロード以外の時)　*/
    // value[0] = 項目名 value[1] = 判定対象文字列
    function FILE_NAME_CHECK_BY_NOUPLOAD( $value ) {			// 入力文字が英数字,"_","-"以外ならエラーを返す
        if(isset($this->arrErr[$value[1]])) {
            return;
        }
        $this->createParam($value);
        if( strlen($this->arrParam[$value[1]]) > 0 && ! EregI("^[[:alnum:]_\.-]+$", $this->arrParam[$value[1]]) || EregI("[\\]" ,$this->arrParam[$value[1]])) {
            $this->arrErr[$value[1]] = $value[0] . "的文件名不能使用中文";
        }
    }

    //日付チェック
    // value[0] = 項目名
    // value[1] = YYYY
    // value[2] = MM
    // value[3] = DD
    function CHECK_DATE($value) {
        if(isset($this->arrErr[$value[1]])) {
            return;
        }
        $this->createParam($value);
        // 少なくともどれか一つが入力されている。
        if($this->arrParam[$value[1]] > 0 || $this->arrParam[$value[2]] > 0 || $this->arrParam[$value[3]] > 0) {
            // 年月日のどれかが入力されていない。
            if(!(strlen($this->arrParam[$value[1]]) > 0 && strlen($this->arrParam[$value[2]]) > 0 && strlen($this->arrParam[$value[3]]) > 0)) {
                $this->arrErr[$value[1]] = $value[0] . "需要完整输入";
            } else if ( ! checkdate($this->arrParam[$value[2]], $this->arrParam[$value[3]], $this->arrParam[$value[1]])) {
                $this->arrErr[$value[1]] = $value[0] . "格式不正确";
            }
        }
    }

    //日付チェック
    // value[0] = 項目名
    // value[1] = YYYY
    // value[2] = MM
    // value[3] = DD
    // value[4] = HH
    // value[5] = mm
    function CHECK_DATE2($value) {
        if(isset($this->arrErr[$value[1]])) {
            return;
        }
        $this->createParam($value);
        // 少なくともどれか一つが入力されている。
        if($this->arrParam[$value[1]] > 0 || $this->arrParam[$value[2]] > 0 || $this->arrParam[$value[3]] > 0 || $this->arrParam[$value[4]] >= 0 || $this->arrParam[$value[5]] >= 0) {
            // 年月日時のどれかが入力されていない。
            if(!(strlen($this->arrParam[$value[1]]) > 0 && strlen($this->arrParam[$value[2]]) > 0 && strlen($this->arrParam[$value[3]]) > 0 && strlen($this->arrParam[$value[4]]) > 0 && strlen($this->arrParam[$value[5]]) > 0 )) {
                $this->arrErr[$value[1]] = $value[0] . "需要完整输入";
            } else if ( ! checkdate($this->arrParam[$value[2]], $this->arrParam[$value[3]], $this->arrParam[$value[1]])) {
                $this->arrErr[$value[1]] = $value[0] . "格式不正确";
            }
        }
    }

    //日付チェック
    // value[0] = 項目名
    // value[1] = YYYY
    // value[2] = MM
    function CHECK_DATE3($value) {
        if(isset($this->arrErr[$value[1]])) {
            return;
        }
        $this->createParam($value);
        // 少なくともどれか一つが入力されている。
        if($this->arrParam[$value[1]] > 0 || $this->arrParam[$value[2]] > 0) {
            // 年月日時のどれかが入力されていない。
            if(!(strlen($this->arrParam[$value[1]]) > 0 && strlen($this->arrParam[$value[2]]) > 0)) {
                $this->arrErr[$value[1]] = $value[0] . "需要完整输入";
            } else if ( ! checkdate($this->arrParam[$value[2]], 1, $this->arrParam[$value[1]])) {
                $this->arrErr[$value[1]] = $value[0] . "格式不正确";
            }
        }
    }

    /*-----------------------------------------------------------------*/
    /*	CHECK_SET_TERM
    /*	年月日に別れた2つの期間の妥当性をチェックし、整合性と期間を返す
    /*　引数 (開始年,開始月,開始日,終了年,終了月,終了日)
    /*　戻値 array(１，２，３）
    /*  		１．開始年月日 (YYYYMMDD 000000)
    /*			２．終了年月日 (YYYYMMDD 235959)
    /*			３．エラー ( 0 = OK, 1 = NG )
    /*-----------------------------------------------------------------*/
    // value[0] = 項目名1
    // value[1] = 項目名2
    // value[2] = start_year
    // value[3] = start_month
    // value[4] = start_day
    // value[5] = end_year
    // value[6] = end_month
    // value[7] = end_day
    function CHECK_SET_TERM ($value) {

        // 期間指定
        if(isset($this->arrErr[$value[2]]) || isset($this->arrErr[$value[5]])) {
            return;
        }
        $this->createParam($value);
        $error = 0;
        if ( (strlen($this->arrParam[$value[2]]) > 0 || strlen($this->arrParam[$value[3]]) > 0 || strlen($this->arrParam[$value[4]] ) > 0) && ! checkdate($this->arrParam[$value[3]], $this->arrParam[$value[4]], $this->arrParam[$value[2]]) ) {
            $this->arrErr[$value[2]] = $value[0] . "不正确";
        }
        if ( (strlen($this->arrParam[$value[5]]) > 0 || strlen($this->arrParam[$value[6]]) > 0 || strlen($this->arrParam[$value[7]] ) > 0) && ! checkdate($this->arrParam[$value[6]], $this->arrParam[$value[7]], $this->arrParam[$value[5]]) ) {
            $this->arrErr[$value[5]] = $value[1] . "不正确";
        }
        if ( (strlen($this->arrParam[$value[2]]) > 0 && strlen($this->arrParam[$value[3]]) > 0 && strlen($this->arrParam[$value[4]] ) > 0) &&  (strlen($this->arrParam[$value[5]]) > 0 || strlen($this->arrParam[$value[6]]) > 0 || strlen($this->arrParam[$value[7]] ) > 0) ){

            $date1 = $this->arrParam[$value[2]] .sprintf("%02d", $this->arrParam[$value[3]]) .sprintf("%02d",$this->arrParam[$value[4]]) ."000000";
            $date2 = $this->arrParam[$value[5]] .sprintf("%02d", $this->arrParam[$value[6]]) .sprintf("%02d",$this->arrParam[$value[7]]) ."235959";

            if (($this->arrErr[$value[2]] == "" && $this->arrErr[$value[5]] == "") && $date1 > $date2) {
                $this->arrErr[$value[2]] = $value[0]. "和" .$value[1]. "的期间不合法";
            }
        }
    }

    /*-----------------------------------------------------------------*/
    /*	CHECK_SET_TERM2
    /*	年月日時に別れた2つの期間の妥当性をチェックし、整合性と期間を返す
    /*　引数 (開始年,開始月,開始日,開始時間,開始分,開始秒,
    /*        終了年,終了月,終了日,終了時間,終了分,終了秒)
    /*　戻値 array(１，２，３）
    /*  		１．開始年月日 (YYYYMMDDHHmmss)
    /*			２．終了年月日 (YYYYMMDDHHmmss)
    /*			３．エラー ( 0 = OK, 1 = NG )
    /*-----------------------------------------------------------------*/
    // value[0] = 項目名1
    // value[1] = 項目名2
    // value[2] = start_year
    // value[3] = start_month
    // value[4] = start_day
    // value[5] = start_hour
    // value[6] = start_minute
    // value[7] = start_second
    // value[8] = end_year
    // value[9] = end_month
    // value[10] = end_day
    // value[11] = end_hour
    // value[12] = end_minute
    // value[13] = end_second

    /*-----------------------------------------------------------------*/
    function CHECK_SET_TERM2 ($value) {

        // 期間指定
        if(isset($this->arrErr[$value[2]]) || isset($this->arrErr[$value[8]])) {
            return;
        }
        $this->createParam($value);
        $error = 0;
        if ( (strlen($this->arrParam[$value[2]]) > 0 || strlen($this->arrParam[$value[3]]) > 0 || strlen($this->arrParam[$value[4]] ) > 0 || strlen($this->arrParam[$value[5]]) > 0) && ! checkdate($this->arrParam[$value[3]], $this->arrParam[$value[4]], $this->arrParam[$value[2]]) ) {
            $this->arrErr[$value[2]] = $value[0] . "不正确";
        }
        if ( (strlen($this->arrParam[$value[8]]) > 0 || strlen($this->arrParam[$value[9]]) > 0 || strlen($this->arrParam[$value[10]] ) > 0 || strlen($this->arrParam[$value[11]] ) > 0) && ! checkdate($this->arrParam[$value[9]], $this->arrParam[$value[10]], $this->arrParam[$value[8]]) ) {
            $this->arrErr[$value[8]] = $value[1] . "不正确";
        }
        if ( (strlen($this->arrParam[$value[2]]) > 0 && strlen($this->arrParam[$value[3]]) > 0 && strlen($this->arrParam[$value[4]] ) > 0 && strlen($this->arrParam[$value[5]] ) > 0) &&  (strlen($this->arrParam[$value[8]]) > 0 || strlen($this->arrParam[$value[9]]) > 0 || strlen($this->arrParam[$value[10]] ) > 0 || strlen($this->arrParam[$value[11]] ) > 0) ){

            $date1 = $this->arrParam[$value[2]] .sprintf("%02d", $this->arrParam[$value[3]]) .sprintf("%02d",$this->arrParam[$value[4]]) .sprintf("%02d",$this->arrParam[$value[5]]).sprintf("%02d",$this->arrParam[$value[6]]).sprintf("%02d",$this->arrParam[$value[7]]);
            $date2 = $this->arrParam[$value[8]] .sprintf("%02d", $this->arrParam[$value[9]]) .sprintf("%02d",$this->arrParam[$value[10]]) .sprintf("%02d",$this->arrParam[$value[11]]).sprintf("%02d",$this->arrParam[$value[12]]).sprintf("%02d",$this->arrParam[$value[13]]);

            if (($this->arrErr[$value[2]] == "" && $this->arrErr[$value[8]] == "") && $date1 > $date2) {
                $this->arrErr[$value[2]] = $value[0]. "和" .$value[1]. "的期间不合法";
            }
            if($date1 == $date2) {
                $this->arrErr[$value[2]] = $value[0]. "和" .$value[1]. "的期间不合法";
            }

        }
    }

    /*-----------------------------------------------------------------*/
    /*	CHECK_SET_TERM3
    /*	年月に別れた2つの期間の妥当性をチェックし、整合性と期間を返す
    /*　引数 (開始年,開始月,終了年,終了月)
    /*　戻値 array(１，２，３）
    /*  		１．開始年月日 (YYYYMMDD 000000)
    /*			２．終了年月日 (YYYYMMDD 235959)
    /*			３．エラー ( 0 = OK, 1 = NG )
    /*-----------------------------------------------------------------*/
    // value[0] = 項目名1
    // value[1] = 項目名2
    // value[2] = start_year
    // value[3] = start_month
    // value[4] = end_year
    // value[5] = end_month
    function CHECK_SET_TERM3 ($value) {

        // 期間指定
        if(isset($this->arrErr[$value[2]]) || isset($this->arrErr[$value[4]])) {
            return;
        }
        $this->createParam($value);
        $error = 0;
        if ( (strlen($this->arrParam[$value[2]]) > 0 || strlen($this->arrParam[$value[3]]) > 0) && ! checkdate($this->arrParam[$value[3]], 1, $this->arrParam[$value[2]]) ) {
            $this->arrErr[$value[2]] = $value[0] . "不正确";
        }
        if ( (strlen($this->arrParam[$value[4]]) > 0 || strlen($this->arrParam[$value[5]]) > 0) && ! checkdate($this->arrParam[$value[5]], 1, $this->arrParam[$value[4]]) ) {
            $this->arrErr[$value[4]] = $value[1] . "不正确";
        }
        if ( (strlen($this->arrParam[$value[2]]) > 0 && strlen($this->arrParam[$value[3]]) > 0 && (strlen($this->arrParam[$value[4]]) > 0 || strlen($this->arrParam[$value[5]]) > 0 ))) {

            $date1 = $this->arrParam[$value[2]] .sprintf("%02d", $this->arrParam[$value[3]]);
            $date2 = $this->arrParam[$value[4]] .sprintf("%02d", $this->arrParam[$value[5]]);

            if (($this->arrErr[$value[2]] == "" && $this->arrErr[$value[5]] == "") && $date1 > $date2) {
                $this->arrErr[$value[2]] = $value[0]. "和" .$value[1]. "的期间不合法";
            }
        }
    }

    //ディレクトリ存在チェック
    function DIR_CHECK ($value) {
        if(isset($this->arrErr[$value[1]])) {
            return;
        }
        $this->createParam($value);
        if(!is_dir($this->arrParam[$value[1]])) {
            $this->arrErr[$value[1]] = "指定的" . $value[0] . "不存在";
        }
    }

    //ディレクトリ存在チェック
    function DOMAIN_CHECK ($value) {
        if(isset($this->arrErr[$value[1]])) {
            return;
        }
        if(strlen($this->arrParam[$value[1]]) > 0 && !ereg("^\.[^.]+\..+", $this->arrParam[$value[1]])) {
            $this->arrErr[$value[1]] = $value[0] . "的格式不正确";
        }
    }

    /*　携帯メールアドレスの判定　*/
    // value[0] = 項目名 value[1] = 判定対象メールアドレス
    function MOBILE_EMAIL_CHECK( $value ){				//　メールアドレスを正規表現で判定する
        if(isset($this->arrErr[$value[1]])) {
            return;
        }
        $this->createParam($value);
        $objMobile = new SC_Helper_Mobile_Ex();
        if(strlen($this->arrParam[$value[1]]) > 0 && !$objMobile->gfIsMobileMailAddress($this->arrParam[$value[1]])) {
            $this->arrErr[$value[1]] = $value[0] . "必须是日本手机号码";
        }
    }
    /**
     * 禁止文字列のチェック
     * value[0] = 項目名 value[1] = 判定対象文字列
     * value[2] = 入力を禁止する文字列(配列)
     *
     * @example $objErr->doFunc(array("URL", "contents", $arrReviewDenyURL), array("PROHIBITED_STR_CHECK"));
     */
    function PROHIBITED_STR_CHECK( $value ) {
        if( isset($this->arrErr[$value[1]]) || empty($this->arrParam[$value[1]]) ) {
            return;
        }
        $this->createParam($value);
        $targetStr     = $this->arrParam[$value[1]];
        $prohibitedStr = str_replace(array('|', '/'), array('\|', '\/'), $value[2]);

        $pattern = '/' . join('|', $prohibitedStr) . '/i';
        if(preg_match_all($pattern, $this->arrParam[$value[1]], $matches)) {
            $this->arrErr[$value[1]] = $value[0] . "包含禁止输入的内容";
        }
    }

    /**
     * PHPコードとして評価可能かチェックする.
     *
     * @access private
     * @param array $value [0] => 項目名, [1] => 評価する文字列
     * @return void
     */
    function EVAL_CHECK($value) {
        if (isset($this->arrErr[$value[0]])) {
            return;
        }
        $this->createParam($value);
        if ($this->evalCheck($value[1]) === false) {
            $this->arrErr[$value[0]] = $value[0] . " 的格式无效";
        }
    }

    /**
     * $value が PHPコードとして評価可能かチェックする.
     *
     * @access private
     * @param mixed PHPコードとして評価する文字列
     * @return mixed PHPコードとして評価できない場合 false,
     *               評価可能な場合は評価した値
     */
    function evalCheck($value) {
    	// falseは、正当な式と評価する。
    	if($value === "false") {
    		return true;
    	}
        return @eval("return " . $value . ";");
    }

    /**
     * 未定義の $this->arrParam に空要素を代入する.
     *
     * @access private
     * @param array $value 配列
     * @return void
     */
    function createParam($value) {
        foreach ($value as $key) {
            if (is_string($key) || is_int($key)) {
                if (!isset($this->arrParam[$key]))  $this->arrParam[$key] = "";
            }
        }
    }
    
    function CALL_CHECK($value){
        if(isset($this->arrErr[$value[1]])) {
            return;
        }		
        $this->createParam($value);
        if(!preg_match_all('/(^(\d{2,4}[-_－—])?\d{3,8}([-_－—]\d{7,8})?([-_－—]\d{1,7})?$)|(^0?(13[0-9]|14[0-9]|17[0-9]|15[^4\D]|18[0-9])\d{8}$)/',$this->arrParam[$value[1]],$matches)){
             $this->arrErr[$value[1]] = $value[0] . "格式不正确";
        }
    }
}
?>
