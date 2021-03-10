<?php
$sql = "SELECT * FROM Calendario WHERE data > '2019-12-01' ORDER BY data ASC";
$st = $conn->prepare($sql);
$st->execute();
$rs=$st->fetchAll();
if (isset($_GET["data"])) {
	$data = $_GET["data"];
} else {
	$data = "";
}
?>
<div class="grid-x grid-padding-x">
	<div class="large-4">
		<form action="" method="GET">
		    <?php
		    if (isset($_GET["imprimir"])) {
		        echo '<input type="hidden" name="imprimir" id="imprimir" value="1" />';
		    }
		    ?>
			<label>Dia da entrega</label>
			<select id="data" name="data">
			<option value="">Escolha</option>
			<?php
			foreach ($rs as $row) {
			    //1a Comunidade
			    $comunidade1 = "";
			    if (getFreq($row["1acomunidade"],"s")) {
			        $comunidade1 .= "S";
			    }
			    if (getFreq($row["1acomunidade"],"q")) {
			        $comunidade1 .= (strlen($comunidade1) > 0) ? "+" : "";
			        $comunidade1 .= "Q";
			    }
			    if (getFreq($row["1acomunidade"],"m")) {
			        $comunidade1 .= (strlen($comunidade1) > 0) ? "+" : "";
			        $comunidade1 .= "M";
			    }
			    //2a Comunidade
			    $comunidade2 = "";
			    if (getFreq($row["2acomunidade"],"s")) {
			        $comunidade2 .= "S";
			    }
			    if (getFreq($row["2acomunidade"],"q")) {
			        $comunidade2 .= (strlen($comunidade2) > 0) ? "+" : "";
			        $comunidade2 .= "Q";
			    }
			    if (getFreq($row["2acomunidade"],"m")) {
			        $comunidade2 .= (strlen($comunidade2) > 0) ? "+" : "";
			        $comunidade2 .= "M";
			    }
			    $frequenciaMenu = "1(".$comunidade1.") | 2(".$comunidade2.")";
			    if ($data == $row["id"]) {
					//echo '<option value="'.$row["id"].'" selected="selected">'.date("d/m/Y",strtotime($row["data"])).' - '.$frequenciaMenu.'</option>';
					echo '<option value="'.$row["id"].'" selected="selected">'.date("d/m/Y",strtotime($row["data"])).'</option>';
				} else {
					//echo '<option value="'.$row["id"].'">'.date("d/m/Y",strtotime($row["data"])).' - '.$frequenciaMenu.'</option>';
					echo '<option value="'.$row["id"].'">'.date("d/m/Y",strtotime($row["data"])).'</option>';
				}
			}
			?>
			</select>
			<input type="submit" name="Enviar" id="Enviar" value="Enviar" />
		<?php
		if (strlen($data) > 0) {
		    echo "<br>";
		    $freqMenu="";
		    foreach (getFrequencias($conn,$data) as $grupo=>$freq) {
		        if (strlen($freqMenu) > 0) { $freqMenu .= " | "; }
		        $freqMenu .= "G".$grupo." = ".getFreqMenu($freq);
		    }
		    if (strlen($freqMenu) > 0) { echo $freqMenu; }
		}
		?>
		</form>
		
	</div>
	<div class="large-8">
	</div>
</div>