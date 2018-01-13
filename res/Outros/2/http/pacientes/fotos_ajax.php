<?php
   /**
    * Gerenciador Clínico Odontológico
    * Copyright (C) 2006 - 2009
    * Autores: Ivis Silva Andrade - Engenharia e Design(ivis@expandweb.com)
    *          Pedro Henrique Braga Moreira - Engenharia e Programação(ikkinet@gmail.com)
    *
    * Este arquivo é parte do programa Gerenciador Clínico Odontológico
    *
    * Gerenciador Clínico Odontológico é um software livre; você pode
    * redistribuí-lo e/ou modificá-lo dentro dos termos da Licença
    * Pública Geral GNU como publicada pela Fundação do Software Livre
    * (FSF); na versão 2 da Licença invariavelmente.
    *
    * Este programa é distribuído na esperança que possa ser útil,
    * mas SEM NENHUMA GARANTIA; sem uma garantia implícita de ADEQUAÇÂO
    * a qualquer MERCADO ou APLICAÇÃO EM PARTICULAR. Veja a
    * Licença Pública Geral GNU para maiores detalhes.
    *
    * Você recebeu uma cópia da Licença Pública Geral GNU,
    * que está localizada na raíz do programa no arquivo COPYING ou COPYING.TXT
    * junto com este programa. Se não, visite o endereço para maiores informações:
    * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html (Inglês)
    * http://www.magnux.org/doc/GPL-pt_BR.txt (Português - Brasil)
    *
    * Em caso de dúvidas quanto ao software ou quanto à licença, visite o
    * endereço eletrônico ou envie-nos um e-mail:
    *
    * http://www.smileodonto.com.br/gco
    * smile@smileodonto.com.br
    *
    * Ou envie sua carta para o endereço:
    *
    * Smile Odontolóogia
    * Rua Laudemira Maria de Jesus, 51 - Lourdes
    * Arcos - MG - CEP 35588-000
    *
    *
    */
	include "../lib/config.inc.php";
	include "../lib/func.inc.php";
	include "../lib/classes.inc.php";
	require_once '../lang/'.$idioma.'.php';
	header("Content-type: text/html; charset=UTF-8", true);
	if(!checklog()) {
		die($frase_log);
	}
	$strUpCase = "ALTERAÇÂO";
	$strLoCase = encontra_valor('pacientes', 'codigo', $_GET[codigo], 'nome').' - '.$_GET['codigo'];
	$acao = '&acao=editar';
?>
 
<div class="conteudo" id="table dados">
 
<h3><?php echo $LANG['patients']['photos']?></h3>
  
    
     <iframe height="300" scrolling="No" border="no" width="200" name="foto_frame" id="foto_frame" src="pacientes/fotos.php?codigo=<?php echo $row[codigo]?><?php echo (($_GET[acao] != 'editar')?'&disabled=yes':'')?>" frameborder="no"></iframe>
    
    
<?php
	$i = 0;
	$query = mysql_query("SELECT * FROM `fotospacientes` WHERE `codigo_paciente` = '".$_GET[codigo]."' ORDER BY `codigo`") or die(mysql_error());	while($row = mysql_fetch_array($query)) {
		if($i % 2 === 0) {
			echo '</tr><tr>';
		}
            ?>
              
               <img class="img-circle" src="pacientes/verfoto.php?codigo=<?php echo $row['codigo']?>" border="0"><BR>
               <font size="1"><?php echo $row['legenda']?></font><br><br>
               <?php echo ((verifica_nivel('pacientes', 'E'))?'<a href="pacientes/excluirfotos_ajax.php?codigo='.$_GET[codigo].'&codigo_foto='.$row[codigo].'" onclick="return confirmLink(this)" target="iframe_upload">'.$LANG['patients']['delete_photo'].'</a>':'')?>
               
            <?php
		$i++;
	}
            ?>

           
        <iframe name="iframe_upload" width="1" height="1" frameborder="0" scrolling="No"></iframe>
          <form id="form2" name="form2" method="POST" action="pacientes/incluirfotos_ajax.php?codigo=<?php echo $_GET['codigo']?>" enctype="multipart/form-data" target="iframe_upload">

              <?php echo $LANG['patients']['file']?> 
                <input type="file" size="20" name="arquivo" id="arquivo" class="forms" <?php echo $disable?> />
 
                  <?php echo $LANG['patients']['legend']?> 
                <input type="text" size="33" name="legenda" id="legenda" class="forms" <?php echo $disable?> />
 
                <input type="submit" name="Salvar" id="Salvar" value="<?php echo $LANG['patients']['save']?>" class="forms" <?php echo $disable?> />
    
         
          </form>
       