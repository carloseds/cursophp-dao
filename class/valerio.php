<?php
session_start();
if (!function_exists('PlaceToRoot')) {
    function PlaceToRoot(){
        $tmp = dirname($_SERVER['PHP_SELF']);
        $tmp = str_replace('\\', '/', $tmp);
        $tmp = explode('/', $tmp);
        $relpath = NULL;
        for ($i = 0; $i < count($tmp); $i++) {
            if ($tmp[$i] != '')
                $relpath .= '../';
        }
        if ($relpath == NULL)
            $relpath = './';

            return $relpath;
    }
}

require_once PlaceToRoot().'config.php';
require_once PlaceToRoot().'Config/CheckLogin.php';
if (!(count(array_intersect(array(1,3),$USER_GROUPS)) > 0 || $_SESSION['nome'] =='VALERIO' || $_SESSION['nome'] =='VERA') ){
    header("location: ".PlaceToRoot()."index.php");
}

function getWorkingDays($startDate, $endDate)
{
    $begin = strtotime($startDate);
    $end   = strtotime($endDate);
    if ($begin > $end) {
        echo "startdate is in the future! <br />";

        return 0;
    } else {
        $no_days  = 0;
        $weekends = 0;
        while ($begin <= $end) {
            $no_days++; // no of days in the given interval
            $what_day = date("N", $begin);
            if ($what_day > 5) { // 6 and 7 are weekend days
                $weekends++;
            };
            $begin += 86400; // +1 day
        };
        $working_days = $no_days - $weekends;

        return $working_days;
    }
}
?>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Comissão Valério</title>
	<!-- Bootstrap CSS -->
  <link href="<?php echo PlaceToRoot(); ?>css/bootstrap.min.css" rel="stylesheet">
  <!--link href="assets/css/style.css" rel="stylesheet"-->
  <!-- jQuery (necessario para os plugins Javascript do Bootstrap) -->
  <script src="<?php echo PlaceToRoot(); ?>js/jquery.js" type="text/javascript"></script>
  <script src="<?php echo PlaceToRoot(); ?>js/bootstrap.min.js" type="text/javascript">  </script>

	<style>
			tr:hover{background-color:#f5f5f5}
	</style>
	<script src="<?php echo PlaceToRoot(); ?>js/Chart/Chart.bundle.js"></script>
	<script src="<?php echo PlaceToRoot(); ?>js/Chart/utils.js"></script>
    <style>
    canvas {
        -moz-user-select: none;
        -webkit-user-select: none;
        -ms-user-select: none;
    }
    .center {
      text-align:center;
        margin: 0 auto !important;
        float: none !important;
    }
    </style>
</head>
<body>

<?php
require PlaceToRoot().'menu.php';
?>


<div class="container">
	<div class="row">
		<div class="col-sm-6 col-md-4 col-md-offset-4">
			<div class="account-wall text-center">
			<h4 class="text-center login-title">Comissão Valério</h4><br/>
			<a href="ComissaoValerioDetalhamento.php" class="text-center login-title">Detalhamento</a><br/>
			</div>
		</div>
	</div>
</div>

<?php
try{
/**************Configurations ****************/
$ValComGru=0.01;
$ValComInd=0.05;
//$DataInicial='20170320';
$DataInicial=(date('Y')-1).date('m').'20';
/*********************************************/
$PercentComGru=sprintf("%.2f%%",  $ValComGru* 100);
$PercentComInd=sprintf("%.2f%%",  $ValComInd* 100);

echo '<div class="container">';
echo '<div style="display:inline-block;">';
				echo '<br/><br/><div style="width:30%" class="container">			<table class="table table-bordered">';
				echo '<tbody> ';
				echo '<tr>';
				echo '<td align="middle" style=" font-family:verdana; font-size:15px;font-weight:bold;" >Valor Comissão Grupo</td>';
				echo '<td align="middle" style=" font-family:verdana; font-size:15px;" >'.$PercentComGru.'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td align="middle" style=" font-family:verdana; font-size:15px;font-weight:bold;" >Valor Comissão Individual</td>';
				echo '<td align="middle" style=" font-family:verdana; font-size:15px;" >'.$PercentComInd.'</td>';
				echo '</tr>';
			echo '</tbody></table></div><br/><br/><br/><br/>';
echo '</div>';


	// Vendas feitas pelos representantes.
	$sql = $db->prepare("
	SELECT [VALOR PEDIDO]/[NUMERO PARCELAS] AS [VALOR PARCELAS], [DATA RECEBIDO] FROM(
        SELECT
        A.NOME_FOR AS NOME
        ,A.CHAVE_PED AS PEDIDO
        ,A.SIT_PED AS [SITACAO PEDIDO]
        ,A.BASETOTAL_PED AS [VALOR PEDIDO]
        ,A.VLCOMTOTAL_PED AS [VALOR TOTAL COMISSAO]
        ,CASE WHEN C.CHAVE_CTA IS NOT NULL THEN C.CHAVE_CTA ELSE E.CHAVE_CTA END AS [CHAVE CONTA]
        ,CASE WHEN C.SIT_CTA IS NOT NULL THEN C.SIT_CTA ELSE E.SIT_CTA END  AS [SITUACAO RECEBIMENTO]
        ,CAST(CASE WHEN C.PARCELAS_CTA=0 THEN 1 ELSE COALESCE(C.PARCELAS_CTA,1) END AS INT) AS [NUMERO PARCELAS]
        ,A.VLCOMTOTAL_PED/CASE WHEN C.PARCELAS_CTA=0 THEN 1 ELSE COALESCE(C.PARCELAS_CTA,1) END AS [VALOR COMISSAO PARCELADA]
        ,A.DESCR_GRUFOR AS GRUPO
        ,COALESCE(convert(varchar,C.DTV_CTA,3),convert(varchar,E.DTV_CTA,3)) AS [DATA VENCIMENTO]
        ,COALESCE(convert(varchar,F.[DATA],112),CASE WHEN E.BLOQUEADO_CTA = 0 THEN convert(varchar,E.DTV_CTA,112) END) AS [DATA RECEBIDO]
        FROM (SELECT D.NOME_FOR,A.CHAVE_PED,A.SIT_PED,A.BASEPRO_PED+A.BASEOVER_PED AS BASETOTAL_PED,A.VLCOMTOTAL_PED,C.DESCR_GRUFOR FROM TPED A
        INNER JOIN TFORGRU B ON A.CHAVE_REPR=B.CHAVE_FOR AND B.CAIXA_FORGRU='CADASTRADO'
        INNER JOIN TGRUFOR C ON B.CHAVE_GRUFOR=C.CHAVE_GRUFOR AND C.CAIXA_GRUFOR='CADASTRADO'
        INNER JOIN TFOR D ON A.CHAVE_REPR=D.CHAVE_FOR AND D.CAIXA_FOR='CADASTRADO'
        WHERE A.CAIXA_PED='CADASTRADO' AND A.DTC_PED >= '2017-04-03 00:00:00.000' AND A.CHAVE_REPR <> '158446'
        AND C.CHAVE_GRUFOR='018'
		and cast(A.CHAVE_PED as int) not in (SELECT CHAVE_PED FROM analista.ComissaoPedidosExcluidos)
        ) A
        LEFT OUTER JOIN TCTA C ON A.CHAVE_PED=C.CHAVE_PED AND C.CAIXA_CTA='CADASTRADO' AND C.TIPO_CTA='RECEBIMENTO' AND C.CONTA_CTBG LIKE '01.01%'
        LEFT OUTER JOIN TCTA E ON A.CHAVE_PED=E.CHAVE_PED AND C.CHAVE_PED IS NULL AND E.CAIXA_CTA='CADASTRADO' AND E.TIPO_CTA='PAGAMENTO' AND E.CONTA_CTBG LIKE '02.02%'
        LEFT OUTER JOIN (SELECT MAX(CASE WHEN DTB_CTA2 IS NULL THEN DTA_CTA2 ELSE DTB_CTA2 END) AS [DATA],CHAVE_CTA FROM TCTA2 WHERE CAIXA_CTA2='CADASTRADO' AND TPVAL_CTA2='COBRADO' GROUP BY CHAVE_CTA)F ON C.CHAVE_CTA=F.CHAVE_CTA
        ) TT
    WHERE [DATA RECEBIDO] IS NOT NULL
	");

	/*$sql = $db->prepare("SELECT A.BASETOTAL_PED/CASE WHEN C.PARCELAS_CTA=0 THEN 1 ELSE COALESCE(C.PARCELAS_CTA,1) END AS [VALOR PARCELAS]
	, COALESCE(convert(varchar,F.[DATA],112),convert(varchar,E.DTV_CTA,3)) AS [DATA RECEBIDO]
	FROM (SELECT A.CHAVE_PED,A.SIT_PED,A.BASEPRO_PED+A.BASEOVER_PED AS BASETOTAL_PED,A.VLCOMTOTAL_PED,C.DESCR_GRUFOR FROM TPED A
	    INNER JOIN TFORGRU B ON A.CHAVE_REPR=B.CHAVE_FOR AND B.CAIXA_FORGRU='CADASTRADO'
	    INNER JOIN TGRUFOR C ON B.CHAVE_GRUFOR=C.CHAVE_GRUFOR AND C.CAIXA_GRUFOR='CADASTRADO'
	    INNER JOIN TFOR D ON A.CHAVE_REPR=D.CHAVE_FOR AND D.CAIXA_FOR='CADASTRADO'
	    WHERE A.CAIXA_PED='CADASTRADO' AND A.DTC_PED >= '2017-04-03 00:00:00.000' AND A.CHAVE_REPR <> '158446'
	    AND C.CHAVE_GRUFOR='018'
	    ) A
	    LEFT OUTER JOIN TCTA C ON A.CHAVE_PED=C.CHAVE_PED AND C.CAIXA_CTA='CADASTRADO' AND C.TIPO_CTA='RECEBIMENTO' AND C.CONTA_CTBG LIKE '01.01%'
				    LEFT OUTER JOIN TCTA E ON A.CHAVE_PED=E.CHAVE_PED AND C.CHAVE_PED IS NULL AND E.CAIXA_CTA='CADASTRADO' AND E.TIPO_CTA='PAGAMENTO' AND E.CONTA_CTBG LIKE '02.02%'
				    LEFT OUTER JOIN (SELECT MAX(CASE WHEN DTB_CTA2 IS NULL THEN DTA_CTA2 ELSE DTB_CTA2 END) AS [DATA],CHAVE_CTA FROM TCTA2 WHERE CAIXA_CTA2='CADASTRADO' AND TPVAL_CTA2='COBRADO' GROUP BY CHAVE_CTA)F ON C.CHAVE_CTA=F.CHAVE_CTA
				    WHERE C.SIT_CTA='RECEBIDO' OR E.SIT_CTA='PAGO'
		");*/

	$DataAtual=$DataInicial;
	if ($sql->execute()) {
		$j=0;
		while ($row = $sql->fetch(PDO::FETCH_ASSOC)) { // Coloca os valores retornados no array [0]=valor [1]=data
			$ArrayValor[$j][0] = $row['VALOR PARCELAS'];
			$ArrayValor[$j][1] = $row['DATA RECEBIDO'];
			$j++;
		}

		for($j=0; $j<12; $j++){ // Para os 12 meses do ano
			$DataAntiga = $DataAtual;
			$DataAtual = AddMonth($DataAtual,1);
			$ValorRep[$j][0]=0;
			$ValorRep[$j][1]=$DataAtual;
			for($i=0; $i<sizeof($ArrayValor); $i++){
				if($ArrayValor[$i][1]<=$DataAtual && $ArrayValor[$i][1]>$DataAntiga){
					$ValorRep[$j][0]=$ValorRep[$j][0]+$ArrayValor[$i][0];
				}

			}
		}
    }
    $ValorRep[$j][0]=0;
	// Vendas feitas pelo Valério
	/*$sql = $db->prepare("
	SELECT A.BASEPRO_PED/CAST(C.PARCELAS_CTA AS INT) AS [VALOR PARCELAS], convert(varchar,F.[DATA],112) AS [DATA RECEBIDO] FROM (
	SELECT *
	FROM TPED A WHERE A.CAIXA_PED='CADASTRADO' AND A.DTC_PED >= '2017-04-03 00:00:00.000'
	AND A.CHAVE_REPR = '158446'
	) A
	LEFT OUTER JOIN TCTA C ON A.CHAVE_PED=C.CHAVE_PED AND C.CAIXA_CTA='CADASTRADO' AND C.TIPO_CTA='RECEBIMENTO' AND C.CONTA_CTBG LIKE '01.01%'
	LEFT OUTER JOIN (SELECT MAX(CASE WHEN DTB_CTA2 IS NULL THEN DTA_CTA2 ELSE DTB_CTA2 END) AS [DATA],CHAVE_CTA FROM TCTA2 WHERE CAIXA_CTA2='CADASTRADO' AND TPVAL_CTA2='COBRADO' GROUP BY CHAVE_CTA)F ON C.CHAVE_CTA=F.CHAVE_CTA
	WHERE C.SIT_CTA='RECEBIDO'
	");*/

	$sql = $db->prepare("SELECT [VALOR PEDIDO]/[NUMERO PARCELAS] AS [VALOR PARCELAS],[DATA RECEBIDO] FROM (SELECT
            	A.CHAVE_PED AS PEDIDO
            	,A.SIT_PED AS [SITACAO PEDIDO]
            	,A.BASEPRO_PED AS [VALOR PEDIDO]
            	,A.VLCOMTOTAL_PED AS [VALOR TOTAL COMISSAO]
            	,CASE WHEN C.CHAVE_CTA IS NOT NULL THEN C.CHAVE_CTA ELSE E.CHAVE_CTA END AS [CHAVE CONTA]
            	,CASE WHEN C.SIT_CTA IS NOT NULL THEN C.SIT_CTA ELSE E.SIT_CTA END  AS [SITUACAO RECEBIMENTO]
            	,CAST(CASE WHEN C.PARCELAS_CTA=0 THEN 1 ELSE COALESCE(C.PARCELAS_CTA,1) END AS INT) AS [NUMERO PARCELAS]
            	,A.VLCOMTOTAL_PED/CASE WHEN C.PARCELAS_CTA=0 THEN 1 ELSE COALESCE(C.PARCELAS_CTA,1) END AS [VALOR COMISSAO PARCELADA]
            	,COALESCE(convert(varchar,C.DTV_CTA,3),convert(varchar,E.DTV_CTA,3)) AS [DATA VENCIMENTO]
            	,COALESCE(convert(varchar,F.[DATA],112),CASE WHEN E.BLOQUEADO_CTA = 0 THEN convert(varchar,E.DTV_CTA,112) END) AS [DATA RECEBIDO]
            FROM (
            SELECT *
            FROM TPED A WHERE A.CAIXA_PED='CADASTRADO' AND A.DTC_PED >= '2017-04-03 00:00:00.000'
            AND A.CHAVE_REPR = '158446'
            and cast(A.CHAVE_PED as int) not in (SELECT CHAVE_PED FROM analista.ComissaoPedidosExcluidos)
            ) A
            LEFT OUTER JOIN TCTA C ON A.CHAVE_PED=C.CHAVE_PED AND C.CAIXA_CTA='CADASTRADO' AND C.TIPO_CTA='RECEBIMENTO' AND C.CONTA_CTBG LIKE '01.01%'
            LEFT OUTER JOIN TCTA E ON A.CHAVE_PED=E.CHAVE_PED AND C.CHAVE_PED IS NULL AND E.CAIXA_CTA='CADASTRADO' AND E.TIPO_CTA='PAGAMENTO' AND E.CONTA_CTBG LIKE '02.02%'
            LEFT OUTER JOIN (SELECT MAX(CASE WHEN DTB_CTA2 IS NULL THEN DTA_CTA2 ELSE DTB_CTA2 END) AS [DATA],CHAVE_CTA FROM TCTA2 WHERE CAIXA_CTA2='CADASTRADO' AND TPVAL_CTA2='COBRADO' GROUP BY CHAVE_CTA)F ON C.CHAVE_CTA=F.CHAVE_CTA
            ) TT
			WHERE [DATA RECEBIDO] IS NOT NULL");

	unset($ArrayValor);
	$DataAtual=$DataInicial;
	if ($sql->execute()) {
		$j=0;
		while ($row = $sql->fetch(PDO::FETCH_ASSOC)) { // Coloca os valores retornados no array [0]=valor [1]=data
			$ArrayValor[$j][0] = $row['VALOR PARCELAS'];
			$ArrayValor[$j][1] = $row['DATA RECEBIDO'];
			$j++;
		}
		for($j=0; $j<12; $j++){ // Para os 12 meses do ano
			$DataAntiga = $DataAtual;
			$DataAtual = AddMonth($DataAtual,1);
			$ValorUsu[$j][0]=0;
			$ValorUsu[$j][1]=$DataAtual;
			for($i=0; $i<sizeof($ArrayValor); $i++){
				if($ArrayValor[$i][1]<=$DataAtual && $ArrayValor[$i][1]>$DataAntiga){
					$ValorUsu[$j][0]=$ValorUsu[$j][0]+$ArrayValor[$i][0];
				}
			}
		}
	}
	// Vendas BIC
	/*$sql = $db->prepare("
SELECT A.VALOR/CAST(C.PARCELAS_CTA AS INT) AS [VALOR PARCELAS], convert(varchar,F.[DATA],112) AS [DATA RECEBIDO] FROM (
	SELECT A.CHAVE_PED,B.SIT_PED, SUM(A.VLT_PEDPRO) AS VALOR, D.COD_PRO AS PRODUTO
	FROM TPEDPRO A
	INNER JOIN TPED B ON A.CHAVE_PED=B.CHAVE_PED AND B.CAIXA_PED='CADASTRADO' AND B.DTC_PED >= '2017-04-03 00:00:00.000' AND A.CAIXA_PEDPRO='CADASTRADO'
	INNER JOIN TPRO D ON A.CHAVE_PRO=D.CHAVE_PRO AND D.CAIXA_PRO='CADASTRADO'
	WHERE B.CHAVE_REPR <> '158446' AND A.CHAVE_PRO IN ('004219','004220','004153','004130','004098','004099','004089','004097','004133','004146','004125','004100','004123','004101','004156','004161','004122','004131','004140','004112','004117','004124','004113','004102','004115','004128','004147','004126','004136','004145','004091','004142','004167','004120','004538','004127','004175','004217','004143','004154','004138','004173','004103','004104','004384','004105','004226','004681','004107','004108','004116','004111','004149','004150','004155','004109','004110','004151','004132','004137','004139','004114','004152','004144')
	GROUP BY A.CHAVE_PED, D.COD_PRO,B.SIT_PED
	) A
	LEFT OUTER JOIN TCTA C ON A.CHAVE_PED=C.CHAVE_PED AND C.CAIXA_CTA='CADASTRADO' AND C.TIPO_CTA='RECEBIMENTO' AND C.CONTA_CTBG LIKE '01.01%'
	LEFT OUTER JOIN (SELECT MAX(CASE WHEN DTB_CTA2 IS NULL THEN DTA_CTA2 ELSE DTB_CTA2 END) AS [DATA],CHAVE_CTA FROM TCTA2 WHERE CAIXA_CTA2='CADASTRADO' AND TPVAL_CTA2='COBRADO' GROUP BY CHAVE_CTA)F ON C.CHAVE_CTA=F.CHAVE_CTA
	WHERE A.CHAVE_PED NOT IN (
			SELECT A.CHAVE_PED
			FROM (SELECT A.CHAVE_PED,A.SIT_PED,A.BASETOTAL_PED,A.VLCOMTOTAL_PED,C.DESCR_GRUFOR FROM TPED A
					INNER JOIN TFORGRU B ON A.CHAVE_REPR=B.CHAVE_FOR AND B.CAIXA_FORGRU='CADASTRADO'
					INNER JOIN TGRUFOR C ON B.CHAVE_GRUFOR=C.CHAVE_GRUFOR AND C.CAIXA_GRUFOR='CADASTRADO'
					WHERE A.CAIXA_PED='CADASTRADO' AND A.DTC_PED >= '2017-04-03 00:00:00.000'
					AND C.CHAVE_GRUFOR='018'
				) A
		)
	AND C.SIT_CTA='RECEBIDO'
	");*/
	/*$sql = $db->prepare("
	SELECT  A.VALOR/CAST(C.PARCELAS_CTA AS INT) AS [VALOR PARCELAS], convert(varchar,F.[DATA],112) AS [DATA RECEBIDO] FROM (
	SELECT A.CHAVE_PED,B.SIT_PED, SUM(A.VLT_PEDPRO) AS VALOR, D.COD_PRO AS PRODUTO, E.DESCR_GRUPRO AS GRUPO
	FROM TPEDPRO A
	INNER JOIN TPED B ON A.CHAVE_PED=B.CHAVE_PED AND B.CAIXA_PED='CADASTRADO' AND B.DTC_PED >= '2017-04-03 00:00:00.000' AND A.CAIXA_PEDPRO='CADASTRADO'
	INNER JOIN TPRO D ON A.CHAVE_PRO=D.CHAVE_PRO AND D.CAIXA_PRO='CADASTRADO'
	INNER JOIN TGRUPRO E ON D.CHAVE_GRUPRO=E.CHAVE_GRUPRO AND E.CAIXA_GRUPRO='CADASTRADO' AND (UPPER(DESCR_GRUPRO) LIKE '% BIC %' OR UPPER(DESCR_GRUPRO) LIKE 'BIC %' OR UPPER(DESCR_GRUPRO) LIKE '% BIC')
	WHERE B.CHAVE_REPR <> '158446'
	GROUP BY A.CHAVE_PED, D.COD_PRO, E.DESCR_GRUPRO,B.SIT_PED
	) A
	LEFT OUTER JOIN TCTA C ON A.CHAVE_PED=C.CHAVE_PED AND C.CAIXA_CTA='CADASTRADO' AND C.TIPO_CTA='RECEBIMENTO' AND C.CONTA_CTBG LIKE '01.01%'
	LEFT OUTER JOIN (SELECT MAX(CASE WHEN DTB_CTA2 IS NULL THEN DTA_CTA2 ELSE DTB_CTA2 END) AS [DATA],CHAVE_CTA FROM TCTA2 WHERE CAIXA_CTA2='CADASTRADO' AND TPVAL_CTA2='COBRADO' GROUP BY CHAVE_CTA)F ON C.CHAVE_CTA=F.CHAVE_CTA
	WHERE A.CHAVE_PED NOT IN (
			SELECT A.CHAVE_PED
			FROM (SELECT A.CHAVE_PED,A.SIT_PED,A.BASETOTAL_PED,A.VLCOMTOTAL_PED,C.DESCR_GRUFOR FROM TPED A
					INNER JOIN TFORGRU B ON A.CHAVE_REPR=B.CHAVE_FOR AND B.CAIXA_FORGRU='CADASTRADO'
					INNER JOIN TGRUFOR C ON B.CHAVE_GRUFOR=C.CHAVE_GRUFOR AND C.CAIXA_GRUFOR='CADASTRADO'
					WHERE A.CAIXA_PED='CADASTRADO' AND A.DTC_PED >= '2017-04-03 00:00:00.000'
					AND C.CHAVE_GRUFOR='018'
				) A
		)
	AND C.SIT_CTA='RECEBIDO'
	");

	/*$sql = $db->prepare("SELECT  A.VALOR/CAST(C.PARCELAS_CTA AS INT) AS [VALOR PARCELAS], convert(varchar,F.[DATA],112) AS [DATA RECEBIDO] FROM (
		SELECT A.CHAVE_PED,B.SIT_PED, SUM(A.VLT_PEDPRO) AS VALOR, D.COD_PRO AS PRODUTO
	FROM TPEDPRO A
	INNER JOIN TPED B ON A.CHAVE_PED=B.CHAVE_PED AND B.CAIXA_PED='CADASTRADO' AND B.DTC_PED >= '2017-04-03 00:00:00.000' AND A.CAIXA_PEDPRO='CADASTRADO'
	INNER JOIN TPRO D ON A.CHAVE_PRO=D.CHAVE_PRO AND D.CAIXA_PRO='CADASTRADO'
	WHERE B.CHAVE_REPR <> '158446' AND A.CHAVE_PRO IN (
	select CHAVE_PRO FROM tprofor where COD_PROFOR in (SELECT cast(id as nvarchar) FROM ANASPOTPRODUTOS)
	AND CAIXA_PROFOR='CADASTRADO')
	GROUP BY A.CHAVE_PED, D.COD_PRO,B.SIT_PED
	) A
	LEFT OUTER JOIN TCTA C ON A.CHAVE_PED=C.CHAVE_PED AND C.CAIXA_CTA='CADASTRADO' AND C.TIPO_CTA='RECEBIMENTO' AND C.CONTA_CTBG LIKE '01.01%'
	LEFT OUTER JOIN (SELECT MAX(CASE WHEN DTB_CTA2 IS NULL THEN DTA_CTA2 ELSE DTB_CTA2 END) AS [DATA],CHAVE_CTA FROM TCTA2 WHERE CAIXA_CTA2='CADASTRADO' AND TPVAL_CTA2='COBRADO' GROUP BY CHAVE_CTA)F ON C.CHAVE_CTA=F.CHAVE_CTA
	WHERE A.CHAVE_PED NOT IN (
			SELECT A.CHAVE_PED
			FROM (SELECT A.CHAVE_PED,A.SIT_PED,A.BASETOTAL_PED,A.VLCOMTOTAL_PED,C.DESCR_GRUFOR FROM TPED A
					INNER JOIN TFORGRU B ON A.CHAVE_REPR=B.CHAVE_FOR AND B.CAIXA_FORGRU='CADASTRADO'
					INNER JOIN TGRUFOR C ON B.CHAVE_GRUFOR=C.CHAVE_GRUFOR AND C.CAIXA_GRUFOR='CADASTRADO'
					WHERE A.CAIXA_PED='CADASTRADO' AND A.DTC_PED >= '2017-04-03 00:00:00.000'
					AND C.CHAVE_GRUFOR='018'
				) A
		)
	AND C.SIT_CTA='RECEBIDO'");*/

	$sql = $db->prepare("			SELECT [VALOR PEDIDO]/[NUMERO PARCELAS] AS [VALOR PARCELAS],[DATA RECEBIDO]  FROM (
			SELECT
        	A.CHAVE_PED AS PEDIDO
        	,A.SIT_PED AS [SITACAO PEDIDO]
        	,A.VALOR AS [VALOR PEDIDO]
        	,CASE WHEN C.CHAVE_CTA IS NOT NULL THEN C.CHAVE_CTA ELSE E.CHAVE_CTA END AS [CHAVE CONTA]
        	,CASE WHEN C.SIT_CTA IS NOT NULL THEN C.SIT_CTA ELSE E.SIT_CTA END  AS [SITUACAO RECEBIMENTO]
        	,A.PRODUTO
        	,CAST(CASE WHEN C.PARCELAS_CTA=0 THEN 1 ELSE COALESCE(C.PARCELAS_CTA,1) END AS INT) AS [NUMERO PARCELAS]
        	,A.VALOR/CASE WHEN C.PARCELAS_CTA=0 THEN 1 ELSE COALESCE(C.PARCELAS_CTA,1) END AS [VALOR COMISSAO PARCELADA]
        	,COALESCE(convert(varchar,C.DTV_CTA,3),convert(varchar,E.DTV_CTA,3)) AS [DATA VENCIMENTO]
        	,COALESCE(convert(varchar,F.[DATA],112),CASE WHEN E.BLOQUEADO_CTA = 0 THEN convert(varchar,E.DTV_CTA,112) END) AS [DATA RECEBIDO]
        FROM (
        SELECT A.CHAVE_PED,B.SIT_PED, SUM(A.VLT_PEDPRO) AS VALOR, D.COD_PRO AS PRODUTO
        FROM TPEDPRO A
        INNER JOIN TPED B ON A.CHAVE_PED=B.CHAVE_PED AND B.CAIXA_PED='CADASTRADO' AND B.DTC_PED >= '2017-04-03 00:00:00.000' AND A.CAIXA_PEDPRO='CADASTRADO'
        INNER JOIN TPRO D ON A.CHAVE_PRO=D.CHAVE_PRO AND D.CAIXA_PRO='CADASTRADO'
        WHERE B.CHAVE_REPR <> '158446'AND A.CHAVE_PRO IN ('004219','004220','004153','004130','004098','004099','004089','004097','004133','004146','004125','004100','004123','004101','004156','004161','004122','004131','004140','004112','004117','004124','004113','004102','004115','004128','004147','004126','004136','004145','004091','004142','004167','004120','004538','004127','004175','004217','004143','004154','004138','004173','004103','004104','004384','004105','004226','004681','004107','004108','004116','004111','004149','004150','004155','004109','004110','004151','004132','004137','004139','004114','004152','004144')
        GROUP BY A.CHAVE_PED, D.COD_PRO,B.SIT_PED
        ) A
        LEFT OUTER JOIN TCTA C ON A.CHAVE_PED=C.CHAVE_PED AND C.CAIXA_CTA='CADASTRADO' AND C.TIPO_CTA='RECEBIMENTO' AND C.CONTA_CTBG LIKE '01.01%'
        LEFT OUTER JOIN TCTA E ON A.CHAVE_PED=E.CHAVE_PED AND C.CHAVE_PED IS NULL AND E.CAIXA_CTA='CADASTRADO' AND E.TIPO_CTA='PAGAMENTO' AND E.CONTA_CTBG LIKE '02.02%'
        LEFT OUTER JOIN (SELECT MAX(CASE WHEN DTB_CTA2 IS NULL THEN DTA_CTA2 ELSE DTB_CTA2 END) AS [DATA],CHAVE_CTA FROM TCTA2 WHERE CAIXA_CTA2='CADASTRADO' AND TPVAL_CTA2='COBRADO' GROUP BY CHAVE_CTA)F ON C.CHAVE_CTA=F.CHAVE_CTA
        WHERE A.CHAVE_PED NOT IN (
        		SELECT A.CHAVE_PED
        		FROM (SELECT A.CHAVE_PED,A.SIT_PED,A.BASETOTAL_PED,A.VLCOMTOTAL_PED,C.DESCR_GRUFOR FROM TPED A
        				INNER JOIN TFORGRU B ON A.CHAVE_REPR=B.CHAVE_FOR AND B.CAIXA_FORGRU='CADASTRADO'
        				INNER JOIN TGRUFOR C ON B.CHAVE_GRUFOR=C.CHAVE_GRUFOR AND C.CAIXA_GRUFOR='CADASTRADO'
        				WHERE A.CAIXA_PED='CADASTRADO' AND A.DTC_PED >= '2017-04-03 00:00:00.000'
        				AND C.CHAVE_GRUFOR='018'
        			) A
        	)
        and cast(A.CHAVE_PED as int) not in (SELECT CHAVE_PED FROM analista.ComissaoPedidosExcluidos)
		) TT
		WHERE [DATA RECEBIDO] IS NOT NULL ");



	unset($ArrayValor);
	$DataAtual=$DataInicial;
	if ($sql->execute()) {
		$j=0;
		while ($row = $sql->fetch(PDO::FETCH_ASSOC)) { // Coloca os valores retornados no array [0]=valor [1]=data
			$ArrayValor[$j][0] = $row['VALOR PARCELAS'];
			$ArrayValor[$j][1] = $row['DATA RECEBIDO'];
			$j++;
		}
		for($j=0; $j<12; $j++){ // Para os 12 meses do ano
			$DataAntiga = $DataAtual;
			$DataAtual = AddMonth($DataAtual,1);
			$ValorBIC[$j][0]=0;
			$ValorBIC[$j][1]=$DataAtual;
			for($i=0; $i<sizeof($ArrayValor); $i++){
				if($ArrayValor[$i][1]<=$DataAtual && $ArrayValor[$i][1]>$DataAntiga){
					$ValorBIC[$j][0]=$ValorBIC[$j][0]+$ArrayValor[$i][0];
				}
			}
		}
	}
	$ValorTotalRep=0;
	for($i=0; $i<sizeof($ValorRep); $i++){
		$ValorTotalRep=$ValorTotalRep+$ValorRep[$i][0];
	}
	$ValorTotalUsu=0;
	for($i=0; $i<sizeof($ValorUsu); $i++){
		$ValorTotalUsu=$ValorTotalUsu+$ValorUsu[$i][0];
	}
	$ValorTotalBIC=0;
	for($i=0; $i<sizeof($ValorBIC); $i++){
		$ValorTotalBIC=$ValorTotalBIC+$ValorBIC[$i][0];
	}
/***************************************************/

echo '<div style="display:inline-block;">';
				echo '<br/><br/><div style="width:50%" class="container">			<table class="table table-bordered">
				<thead>
					<tr>
						<th class="text-center"></th>
						<th class="text-center">Valor Total</th>
						<th class="text-center">Comissão</th>
					</tr>
				</thead>';
				echo '<tbody> ';
				echo '<tr>';
				echo '<td align="middle" style=" font-family:verdana; font-size:15px;font-weight:bold;" >Valor Total Vendas dos Representantes</td>';
				echo '<td align="middle" style=" font-family:verdana; font-size:15px;" >R$'.number_format($ValorTotalRep,2,'.',',').'</td>';
				echo '<td align="middle" style=" font-family:verdana; font-size:15px;" >R$'.number_format($ValorTotalRep*$ValComGru,2,'.',',').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td align="middle" style=" font-family:verdana; font-size:15px;font-weight:bold;" >Valor Total BIC</td>';
				echo '<td align="middle" style=" font-family:verdana; font-size:15px;" >R$'.number_format($ValorTotalBIC,2,'.',',').'</td>';
				echo '<td align="middle" style=" font-family:verdana; font-size:15px;" >R$'.number_format($ValorTotalBIC*$ValComGru,2,'.',',').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td align="middle" style=" font-family:verdana; font-size:15px;font-weight:bold;" >Vendas Realizadas</td>';
				echo '<td align="middle" style=" font-family:verdana; font-size:15px;" >R$'.number_format($ValorTotalUsu,2,'.',',').'</td>';
				echo '<td align="middle" style=" font-family:verdana; font-size:15px;" >R$'.number_format($ValorTotalUsu*$ValComInd,2,'.',',').'</td>';
				echo '</tr>';
				echo '  <tfoot>
				    <tr style="background-color:#a0a0a0">';
				echo '<td align="middle" style=" font-family:verdana; font-size:15px;font-weight:bold;" >Total</td>';
				echo '<td align="middle" style=" font-family:verdana; font-size:15px;" >R$'.number_format($ValorTotalRep+$ValorTotalBIC+$ValorTotalUsu,2,'.',',').'</td>';
				echo '<td align="middle" style=" font-family:verdana; font-size:15px;" >R$'.number_format($ValorTotalRep*$ValComGru+$ValorTotalBIC*$ValComGru+$ValorTotalUsu*$ValComInd,2,'.',',').'</td>';
				echo '    </tr>
				  </tfoot>';

			echo '</tbody></table></div><br/><br/><br/><br/>';
echo '</div>';
echo '</div>';

?>
<hr>
    <div class="center" style="width:75%;">
        <canvas id="canvas"></canvas>
    </div>
        <script>
    var lineChartData = {
        labels: [<?php
		$DataAtual=$DataInicial;
		for($j=0; $j<12; $j++){ // Para os 12 meses do ano
			$DataAtual = AddMonth($DataAtual,1);
			echo '"'.substr($DataAtual, 6,2).'/'.substr($DataAtual, 4,2).'/'.substr($DataAtual, 0,4).'"';
			if($j<>11){
				echo ",";
			}
		}
		?>],
        datasets: [{
            label: "Vendas Valério",
            borderColor: window.chartColors.blue,
            backgroundColor: window.chartColors.blue,
            fill: false,
            data: [<?php
		$DataAtual=$DataInicial;
		for($j=0; $j<12; $j++){ // Para os 12 meses do ano
			$DataAtual = AddMonth($DataAtual,1);
			echo number_format($ValorRep[$j][0]+$ValorUsu[$j][0]+$ValorBIC[$j][0],2,'.','');
			if($j<>11){
				echo ",";
			}
		}
		?>],
            yAxisID: "y-axis-1"
        }, {
            label: "Comissão Valério",
            borderColor: window.chartColors.blue,
            backgroundColor: window.chartColors.blue,
            fill: false,
			borderDash: [3, 5],
            data: [<?php
		$DataAtual=$DataInicial;
		for($j=0; $j<12; $j++){ // Para os 12 meses do ano
			$DataAtual = AddMonth($DataAtual,1);
			echo number_format($ValorRep[$j][0]*$ValComGru+$ValorUsu[$j][0]*$ValComInd+$ValorBIC[$j][0]*$ValComGru,2,'.','');
			if($j<>11){
				echo ",";
			}
		}
		?>],
            yAxisID: "y-axis-1"
        }]
    };

    window.onload = function() {
        var ctx = document.getElementById("canvas").getContext("2d");
        window.myLine = Chart.Line(ctx, {
            data: lineChartData,
            options: {
                responsive: true,
                hoverMode: 'index',
                stacked: false,
                title:{
                    display: true,
                    text:'Resumo por mes'
                },
                scales: {
                    yAxes: [{
                        type: "linear", // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
                        display: true,
                        position: "left",
                        id: "y-axis-1",
                    }/*, {
                        type: "linear", // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
                        display: true,
                        position: "right",
                        id: "y-axis-2",

                        // grid line settings
                        gridLines: {
                            drawOnChartArea: false, // only want the grid lines for one axis to show up
                        },
                       	ticks: {
                		max: 10000,
                		min: 0,
                		}
                    }*/
                    ],
                }
            }
        });
    };
    </script>
<hr>


<div class="container">
	<div class="row">
		<div class="col-sm-6 col-md-4 col-md-offset-4">
			<div class="account-wall text-center">
			<h4 class="text-center login-title">Listagem por mes</h4><br/>
			</div>
		</div>
	</div>
</div>
<?php

echo '<br/><br/><div style="width:50%;left: 50%;" class="container">			<table class="table table-bordered">
				<thead>
					<tr>
						<th class="text-center span2"></th>
						<th class="text-center">Valor Vendas</th>
						<th class="text-center">Comissão Referente</th>
					</tr>
				</thead>
				<tbody> ';
		$DataAtual=$DataInicial;
		for($j=0; $j<12; $j++){ // Para os 12 meses do ano
			$DataAtual = AddMonth($DataAtual,1);
			echo '<tr>';
			echo '<td align="middle" style=" font-family:verdana; font-size:15px; font-weight:bold;" >'.substr($DataAtual, 6,2).'/'.substr($DataAtual, 4,2).'/'.substr($DataAtual, 0,4).'</td>';
			echo '<td align="middle" style=" font-family:verdana; font-size:15px;" >R$'.number_format($ValorRep[$j][0]+$ValorUsu[$j][0]+$ValorBIC[$j][0],2,'.',',').'</td>';
			echo '<td align="middle" style=" font-family:verdana; font-size:15px;" >R$'.number_format($ValorRep[$j][0]*$ValComGru+$ValorUsu[$j][0]*$ValComInd+$ValorBIC[$j][0]*$ValComGru,2,'.',',').'</td>';
			echo '</tr>';

		}
		echo '</tbody></table></div><br/><br/><br/><br/>';

		$sql = $db->prepare("SELECT
	A.NOME_FOR AS REPRESENTANTE
	,D.OBJ_FORTABULEIRO OBJETIVO
	,D.VAL_FORTABULEIRO VALOR
	,CASE WHEN D.OBJ_FORTABULEIRO-D.VAL_FORTABULEIRO<=0 THEN 0 ELSE D.OBJ_FORTABULEIRO-D.VAL_FORTABULEIRO END FALTA
FROM TFOR A
INNER JOIN TFORGRU B ON A.CHAVE_FOR=B.CHAVE_FOR AND A.CAIXA_FOR='CADASTRADO' AND B.CAIXA_FORGRU='CADASTRADO'
INNER JOIN TGRUFOR C ON B.CHAVE_GRUFOR=C.CHAVE_GRUFOR AND C.CAIXA_GRUFOR='CADASTRADO'
LEFT OUTER JOIN TFORTABULEIRO D ON A.CHAVE_FOR=D.CHAVE_FOR AND D.CAIXA_FORTABULEIRO='CADASTRADO' AND D.DTI_FORTABULEIRO=DATEADD(month, 0, DATEADD(month, DATEDIFF(month, 0, GETDATE()), 0)) AND D.DTF_FORTABULEIRO=DATEADD(month, 1, DATEADD(month, DATEDIFF(month, 0, GETDATE()), 0))-1
WHERE B.CHAVE_GRUFOR='018'
ORDER BY A.NOME_FOR ");


		if ($sql->execute()) {
		    echo '<br/><br/><div style="width:50%;left: 50%;" class="container">			<table class="table table-bordered">
				<thead>
					<tr>
						<td class="text-center" colspan="4" style="font-weight: bold;">Meta Mensal</td>
					</tr>
					<tr>
						<th class="text-center">Nome</th>
                        <th class="text-center">Objetivo</th>
                        <th class="text-center">Vendido</th>
                        <th class="text-center">Falta</th>
					</tr>
				</thead>
				<tbody> ';
		    $primeiro = date("Y-m-01");
		    $hoje = date("Y-m-d");
		    $ultimo  = date("Y-m-t");
		    $total = getWorkingDays($primeiro, $ultimo);
		    $passado = getWorkingDays($primeiro, $hoje);

		    while ($row = $sql->fetch(PDO::FETCH_ASSOC)) {
		        if($row['FALTA']==0){
		            $row['COR']=2;
		        }elseif ($row['VALOR']*$total/$passado >=$row['OBJETIVO']){
		            $row['COR']=1;
		        }else{
		            $row['COR']=0;
		        }

		        echo '<tr style="background-color: ' .($row['COR'] ==2 ?  '#008000b0' : ($row['COR'] ==1  ? '#ffff00ba' : '#ff0000a8')).' ">';
		        echo '<td align="middle" style=" font-family:verdana; font-size:15px;" nowrap>'.$row['REPRESENTANTE'].'</td>';
		        echo '<td align="middle" style=" font-family:verdana; font-size:15px;" >'.number_format($row['OBJETIVO'],0,',','.').'</td>';
		        echo '<td align="middle" style=" font-family:verdana; font-size:15px;" >'.number_format($row['VALOR'],0,',','.').'</td>';
		        echo '<td align="middle" style=" font-family:verdana; font-size:15px;" >'.number_format($row['FALTA'],0,',','.').'</td>';
		        echo '</tr>';
		    }
		    echo '</tbody></table></div><br/><br/><br/><br/>';

		}





}catch(Exception $e){
	unset($db);
    unset($query);
	header("location; ".PlaceToRoot()."error.php?".$e->getMessage());
}

?>
<!-- jQuery (necessario para os plugins Javascript do Bootstrap) >
  <script src="js/jquery.js" type="text/javascript"></script>
  <script src="js/bootstrap.min.js" type="text/javascript"></script-->
<?php
require PlaceToRoot().'footer.php';
?>
</body>
</html>