<?php
	include "../lib/config.inc.php";
	include "../lib/func.inc.php";
	include "../lib/classes.inc.php";
	require_once '../lang/'.$idioma.'.php';
	header("Content-type: text/html; charset=UTF-8", true);
	if(!checklog()) { die($frase_log); }

?>

<script>
    function muda_valor(input) {
        if(input.value == 'Sim') input.value = 'Não';else input.value = 'Sim';
    }
    
    function verificaDebito(HTMLElement){
       
            
        var id=false;
        if(HTMLElement.value.split('-').length>1)
            id = parseInt(HTMLElement.value.split('-')[1]);
        
        var line = $(HTMLElement).parents(".agendarow");
        var sym = $(line).find('#debit');
        
        
        if(!id || isNaN(id)) {
            line.removeClass("debito text-danger");
            sym.removeClass('glyphicon-alert glyphicon');
            line.find(".form-control").removeClass("text-danger");
            line.find("#debito").html("");
            line.find("#time").removeClass("text-danger");
            return;
        }
        
        console.log("request")
        
        $.ajax({url: "pacientes/debito_ajax.php?codigo="%2Bid, cache: false})
          .done(function( texto ) {
          if(texto!='false'){ 
                line.addClass("debito text-danger");
                sym.addClass('glyphicon-alert glyphicon');
                line.find(".form-control").addClass("text-danger");
                line.find("#debito").html('R$ '%2Btexto);
                line.find("#time").addClass("text-danger");
            }
            else{ 
                line.removeClass("debito text-danger");
                sym.removeClass('glyphicon-alert glyphicon');
                line.find(".form-control").removeClass("text-danger");
                line.find("#debito").html("");
                line.find("#time").removeClass("text-danger");
            }
          });
      
    }
    
</script> 

<div class="clearfix">
    
<?php

    $debito_total =0 ;

	if(!is_date(converte_data($_GET[pesquisa], 1)) || $_GET[codigo_dentista] == "") die();

    $agenda = new TAgendas();

    // Define as horas mostradas na agenda
    $horas = array('07','08','09','10','11','12','13','14','15','16','17','18','19','20','21','22');
    $minutos = array('00', '15', '30', '45');
    foreach($horas as $hora) foreach($minutos as $minuto) $horario[] = $hora.":".$minuto;

    // verifica os horários de atendimento do dentisa neste dia da semana
    $weekday = date( 'w' , converte_data ( converte_data($_GET['pesquisa'] , 1) , 3));
    $sql = "SELECT * FROM dentista_atendimento WHERE codigo_dentista = " . $_GET['codigo_dentista'] . " AND dia_semana = " . $weekday;
    $atend = mysql_fetch_assoc ( mysql_query ( $sql ) );

    // imprime os dias da agenda
    for($i = 0; $i < count($horario); $i++) {

        $agenda->LoadAgenda(converte_data($_GET[pesquisa], 1), $horario[$i], $_GET[codigo_dentista]);
        if(!$agenda->ExistHorario()) {
            $agenda->SalvarNovo();
        }
        if((converte_data($_GET[pesquisa], 1) < date(Y.'-'.m.'-'.d)) || ($_GET[codigo_dentista] != $_SESSION[codigo] && $_SESSION[nivel] == 'Dentista') || !verifica_nivel('agenda', 'E')) {
            $blur = 'onblur';
            $disable_obs = $disable = 'disabled';
        } else {
            $blur = '';
            $disable_obs = $disable = '';
        }
        if($agenda->RetornaDados('faltou') == 'Sim') {
            $chk = 'checked';
            $val_chk = 'Não';
        } else {
            $chk = '';
            $val_chk = 'Sim';
        }

        if ( $atend['ativo'] <= 0 ) {
            $disable_obs = $disable = 'disabled';
        } else {
            if ( $horario[$i].':00' < $atend['hora_inicio'] || $horario[$i].':00' > $atend['hora_fim'] ) {
                $disable = 'disabled';
                $disable_obs = '';
            }
        }
            
        $pacienteatual=$agenda->RetornaDados('descricao');
        $codigo_pac = $agenda->RetornaDados('codigo_paciente');
        $debito = em_debito($codigo_pac );
        $debito_total += $debito;
        
    ?>

        <div id="linha-agenda" class="agendarow col-xs-12 col-md-6 <?php if($debito){ echo 'debito text-danger'; } ?>">
            <div class="col-xs-9">
                <div class="input-group">
                    <span class="input-group-addon" >
                         <div style="min-width:48px!important" id="time" class="<?php if($debito){ echo 'text-danger' ;} ?>">
                            <a href="#" onclick="Ajax('pacientes/incluir','conteudo','codigo=' %2B $('#codigo_pac<?php echo $i?>').val() %2B '&acao=editar')">
                            <?php echo $horario[$i]?>
                                <span class="glyphicon glyphicon-user"></span>
                            
                            <span id="debito"> <?php if($debito) echo 'R$ '.number_format ($debito,2); ?> </span>
                             </a> 
                        </div>
                    </span>
                    <input <?php echo $disable?> 
                       class="form-control <?php if($debito) echo 'text-danger' ?>" 
                       type="text" 
                       size="30" 
                       maxlength="90" 
                       name="descricao" 
                       onkeyup="searchSuggest(this, 'codigo_pac<?php echo $i?>', 'search<?php echo $i?>');" 
                       id="descricao<?php echo $i?>" 
                       value="<?php echo $pacienteatual ?>" 
                       onfocus="esconde_itens('searches')" 
                       onkeypress="document.getElementById('codigo_pac<?php echo $i?>').value=''" 
                       autocomplete="off" 
                       onblur="$('#codigo_pac<?php echo $i?>').val(this.value.split('-')[1]);
                               verificaDebito(this);
                               if(this.value=='')
                                    Ajax('agenda/atualiza','agenda_atualiza','data=<?php echo $agenda->RetornaDados('data')?>&hora=<?php echo $agenda->RetornaDados('hora')?>:00&descricao='%2Bthis.value%2B'&codigo_dentista=<?php echo $agenda->RetornaDados('codigo_dentista')?>&codigo_paciente=0');
                               else
                                    Ajax('agenda/atualiza','agenda_atualiza','data=<?php echo $agenda->RetornaDados('data')?>&hora=<?php echo $agenda->RetornaDados('hora')?>:00&descricao='%2Bthis.value%2B'&codigo_dentista=<?php echo $agenda->RetornaDados('codigo_dentista')?>&codigo_paciente='%2Bdocument.getElementById('codigo_pac<?php echo $i?>').value);
                               " 
                       /> 
                        <input type="hidden" id="codigo_pac<?php echo $i?>" value="<?php echo $agenda->RetornaDados('codigo_paciente')?>" />
                   <div id='search<?php echo $i?>' style="index:099999"></div>
                      
                </div>
            </div>
            <div class="col-xs-3">
             <div class="input-group">
                <input class="form-control <?php if($debito) echo 'text-danger' ?>" type="text" size="13" maxlength="15" name="procedimento" id="procedimento" value="<?php echo $agenda->RetornaDados('procedimento')?>" <?php echo $disable?> onblur="Ajax('agenda/atualiza', 'agenda_atualiza', 'data=<?php echo $agenda->RetornaDados('data')?>&hora=<?php echo $agenda->RetornaDados('hora')?>:00&procedimento='%2Bthis.value%2B'&codigo_dentista=<?php echo $agenda->RetornaDados('codigo_dentista')?>')" onfocus="esconde_itens('searches')" />
                  
                <span class="input-group-addon">
                    <input class="" type="checkbox" name="faltou" id="faltou" value="<?php echo $val_chk?>" <?php echo $disable.' '.$chk?> onclick="Ajax('agenda/atualiza', 'agenda_atualiza', 'data=<?php echo $agenda->RetornaDados('data')?>&hora=<?php echo $agenda->RetornaDados('hora')?>:00&faltou='%2Bthis.value%2B'&codigo_dentista=<?php echo $agenda->RetornaDados('codigo_dentista')?>'); muda_valor(this);" onfocus="esconde_itens('searches')"  /></span>
                
                 </div>
                 
            </div>
          
          
            
        </div>
            
        <?php
    }

    // ?
    $sql = "SELECT `data`, `obs` FROM agenda_obs WHERE data = '".converte_data($_GET['pesquisa'], 1)."' AND codigo_dentista = '".$_GET['codigo_dentista']."'";
    $query = mysql_query($sql) or die('Line 128: '.mysql_error());
    $row = mysql_fetch_array($query);

    // ?
    if($row['data'] == '') {
        mysql_query("INSERT INTO agenda_obs (data, codigo_dentista) VALUES ('".converte_data($_GET['pesquisa'], 1)."', '".$_GET['codigo_dentista']."')") or die('Line 116: '.mysql_error());
        $sql = "SELECT data, obs FROM agenda_obs WHERE data = ".converte_data($_GET['pesquisa'], 1);
        $query = mysql_query($sql) or die('Line 118: '.mysql_error());
        $row = mysql_fetch_array($query);
    }
?>
	 

</div>

<div class="clearfix">
    <br>
    <?php //imprime debito total do dia
        if($debito_total > 0){ ?>
        <div class="alert alert-danger alert-dismissible" role="alert"> 
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <?php echo $LANG['patients']['patients_in_debt']; ?>
            <strong><?php echo 'R$ '.number_format ($debito_total,2); ?> </strong>
        </div>
    <?php } ?>
</div>
 
<div class="clearfix" id="agendacomprida">

</div>

 <div class="clearfix" >
    <label><?php echo $LANG['calendar']['comments_of_day']?></label>
    <textarea class="form-control" name="observacoes" rows="6" style="overflow:hidden" <?php echo $disable_obs?> onblur='Ajax("agenda/atualizaobs", "agenda_atualiza", "data=<?php echo converte_data($_GET['pesquisa'], 1)?>&codigo_dentista=<?php echo $_GET['codigo_dentista']?>&obs="%2Bthis.value.replace(/\n/g, "<br>"))'>
        <?php echo ereg_replace('<br>', "\n", $row['obs'])?>
    </textarea>
 </div>

<div>
    <a class="btn btn-default" href="relatorios/agenda_consultas.php?data=<?php echo converte_data($_GET[pesquisa], 1)?>&codigo_dentista=<?php echo $_GET[codigo_dentista]?>" target="_blank">
        <span class="glyphicon-print glyphicon" ></span>
        <?php echo $LANG['calendar']['print_calendar']?>
    </a>
</div>

<div id="agenda_atualiza"></div>

<script>
    $('[data-toggle="tooltip"]').tooltip();
</script>