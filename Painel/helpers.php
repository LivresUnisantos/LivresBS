<?php
//Obtém número de grupos ativo
function getTotalGrupos($conn) {
    $sql = "SELECT * FROM Parametros WHERE parametro = 'grupos'";
    $st = $conn->prepare($sql);
	$st->execute();
    $rs = $st->fetch();
    return $rs["valor"];
}
//Obtém dias com entregas ativas
function getDiasEntregas($conn) {
    $sql = "SELECT * FROM Parametros WHERE parametro LIKE 'DiaEntregaG%'";
    $st = $conn->prepare($sql);
	$st->execute();
    $rs = $st->fetchAll();
    $i=0;
    foreach ($rs as $row) {
        if (!is_array($dias)) {
            $dias[$i] = $row["valor"];
            $i++;
        } else {
            if (!in_array($row["valor"],$dias)) {
                $dias[$i] = $row["valor"];
                $i++;
            }
        }
    }
    return $dias;
}
//Retorna array de frequências para determinado dia. $data é o ID da data no calendário
function getFrequencias($conn,$data) {
    $sql = "SELECT * FROM Calendario WHERE id = ".$data;
	$st = $conn->prepare($sql);
	$st->execute();
	if ($st->rowCount() > 0) {
    	$rs=$st->fetchAll();
    	for ($i = 1; $i <= getTotalGrupos($conn); $i++) {
    	    $frequencia[$i] = $rs[0][$i."acomunidade"];
    	}
    	return $frequencia;
	} else {
	    return 0;
	}
}
//Gera a instrução SQL que retorna todos os pedidos da semana
//Função foi adaptada para receber a frequência de cada comunidade no array "$frequencias"
//Para retrocompatibilidade com as páginas existentes com código para duas comunidades apenas, foi criada a variável $frequenciadumb
//Essa variável pega a frequencia da comunidade2 e no runtime a função transforma isso em um array de frequencias na variável $frequencias
//Alteração realizada em 12/02/2020 como preparativo para que o sistema receba múltiplas comunidades
function getSQLPedidoSemana($dataEntrega,$frequencias,$frequenciadumb,$ordem="produtor") {
    $sql = "SELECT * FROM Pedidos LEFT JOIN produtos ON Pedidos.IDProduto = produtos.id";
	$sql .= " LEFT JOIN Consumidores ON Consumidores.id = Pedidos.IDConsumidor";
	$sql .= " WHERE produtos.previsao <= '".date("Y-m-d",$dataEntrega)."'";
	$sql .= " AND Pedidos.Quantidade > 0";
	$sql .= " AND Consumidores.ativo = 1";
	
	//armeng para lidar com antiga e nova forma de frequencia
	if (!is_array($frequencias)) {
	    $aux=$frequencias;
	    unset($frequencias);
	    $frequencias[1] = $aux;
	    $frequencias[2] = $frequenciadumb;
	}
	//Definir clásula de frequência
	foreach($frequencias as $comunidade=>$frequencia) {
	    $freq[$comunidade] = "";
    	if (substr($frequencia,0,1) == 1) {
    		$freq[$comunidade] .= "comunidade = ".$comunidade." AND (Pedidos.frequencia = 'Semanal'";
    	}
    	if (substr($frequencia,1,1) == 1) {
    		if (strlen($freq[$comunidade]) > 0) {
    			$freq[$comunidade] .= " OR ";
    		} else {
    			$freq[$comunidade] .= "comunidade = ".$comunidade." AND (";
    		}
    		$freq[$comunidade] .= "Pedidos.frequencia = 'Quinzenal'";
    	}
    	if (substr($frequencia,2,1) == 1) {
    		if (strlen($freq[$comunidade]) > 0) {
    			$freq[$comunidade] .= " OR ";
    		} else {
    			$freq[$comunidade] .= "comunidade = ".$comunidade." AND (";
    		}
    		$freq[$comunidade] .= "Pedidos.frequencia = 'Mensal'";
    	}
    	if (strlen($freq[$comunidade]) > 0) {
    		$freq[$comunidade] = $freq[$comunidade] . ")";
    	}
	}
	$whereFreq = "";
	foreach ($freq as $f) {
	    if (strlen($f) > 0) {
	        if (strlen($whereFreq) > 0) {
	            $whereFreq .= " OR ";
	        }
	        $whereFreq .= "(".$f.")";
	    }
	}
	if (strlen($whereFreq) > 0) {
	    $sql .= " AND (".$whereFreq.")";
	}
	//Fim frequência
	$ordem = strtolower($ordem);
	if ($ordem == "produtor") {
	    $sql .= " ORDER BY produtos.produtor,produtos.nome";
	}
	if ($ordem == "grupo") {
	    $sql .= " ORDER BY Consumidores.comunidade,Consumidores.consumidor,produtos.nome";
	}
	if ($ordem == "consumidor") {
	    $sql .= " ORDER BY Consumidores.consumidor,produtos.nome";
	}
	return $sql;
}
/*
//Gera a instrução SQL que retorna todos os pedidos da semana
function getSQLPedidoSemanaOld($dataEntrega,$frequencia1,$frequencia2,$ordem="produtor") {
    $sql = "SELECT * FROM Pedidos LEFT JOIN produtos ON Pedidos.IDProduto = produtos.id";
	$sql .= " LEFT JOIN Consumidores ON Consumidores.id = Pedidos.IDConsumidor";
	$sql .= " WHERE produtos.previsao <= '".date("Y-m-d",$dataEntrega)."'";
	$sql .= " AND Pedidos.Quantidade > 0";
	$sql .= " AND Consumidores.ativo = 1";
	//Definir clásula de frequência
	    //1a comunidade
	$freq = "";
	if (substr($frequencia1,0,1) == 1) {
		$freq .= "(comunidade = 1 AND (Pedidos.frequencia = 'Semanal'";
	}
	if (substr($frequencia1,1,1) == 1) {
		if (strlen($freq) > 0) {
			$freq .= " OR ";
		} else {
			$freq .= "(comunidade = 1 AND (";
		}
		$freq .= "Pedidos.frequencia = 'Quinzenal'";
	}
	if (substr($frequencia1,2,1) == 1) {
		if (strlen($freq) > 0) {
			$freq .= " OR ";
		} else {
			$freq .= "(comunidade = 1 AND (";
		}
		$freq .= "Pedidos.frequencia = 'Mensal'";
	}
	if (strlen($freq) > 0) {
		$freq = $freq . "))";
	}
	    //2a comunidade
	$freq2 = "";
	if (substr($frequencia2,0,1) == 1) {
		$freq2 .= "(comunidade = 2 AND (Pedidos.frequencia = 'Semanal'";
	}
	if (substr($frequencia2,1,1) == 1) {
		if (strlen($freq2) > 0) {
			$freq2 .= " OR ";
		} else {
			$freq2 .= "(comunidade = 2 AND (";
		}
		$freq2 .= "Pedidos.frequencia = 'Quinzenal'";
	}
	if (substr($frequencia2,2,1) == 1) {
		if (strlen($freq2) > 0) {
			$freq2 .= " OR ";
		} else {
			$freq2 .= "(comunidade = 2 AND (";
		}
		$freq2 .= "Pedidos.frequencia = 'Mensal'";
	}
	if (strlen($freq2) > 0) {
		$freq2 = $freq2 . "))";
	}
	if (strlen($freq) > 0 || strlen($freq2) > 0) {
	    if (strlen($freq) > 0 && strlen($freq2) > 0) {
	        $freq = $freq . " OR " . $freq2;
	        $sql .= " AND (".$freq.")";
	    } else {
	        if (strlen($freq) > 0) {
	            $sql .= " AND (".$freq.")";
	        } else {
	            $sql .= " AND (".$freq2.")";
	        }
	    }
	}
	//Fim frequência
	$ordem = strtolower($ordem);
	if ($ordem == "produtor") {
	    $sql .= " ORDER BY produtos.produtor,produtos.nome";
	}
	if ($ordem == "grupo") {
	    $sql .= " ORDER BY Consumidores.comunidade,Consumidores.consumidor,produtos.nome";
	}
	if ($ordem == "consumidor") {
	    $sql .= " ORDER BY Consumidores.consumidor,produtos.nome";
	}
	return $sql;
}
*/

//Retorna true/false caso para determinado cÃ³digo de frequÃªncia estÃ¡ habilitado um tipo de frequÃªncia
//$frequencia = 000 / 001 / 100 / 101, etc
//$tipo = s, q ou m (semanal, quinzenal, mensal)
function getFreq($frequencia,$tipo) {
    $tipo = strtolower($tipo);
    $tipoInt="";
    if ($tipo == "s") {
        $tipoInt = substr($frequencia,0,1);
    } else {
        if ($tipo == "q") {
            $tipoInt = substr($frequencia,1,1);
        } else {
            if ($tipo == "m") {
                $tipoInt = substr($frequencia,2,1);
            }
        }
    }
    if ($tipoInt == 1) {
        return true;
    } else {
        return false;
    }
}

function getFreqMenu($frequencia) {
    $freqMenu="";
    if (getFreq($frequencia,"s")) {
        $freqMenu .= "S";
    }
    if (getFreq($frequencia,"q")) {
        $freqMenu .= (strlen($freqMenu) > 0) ? "+" : "";
        $freqMenu .= "Q";
    }
    if (getFreq($frequencia,"m")) {
        $freqMenu .= (strlen($freqMenu) > 0) ? "+" : "";
        $freqMenu .= "M";
    }
    return $freqMenu;
}

//Define os limites de quantidade e a unidade de incremento de acordo com a unidade
function defineLimitesQuantidade($unidade) {
    $unidade = strtolower($unidade);
    $unidade = trim($unidade);
    $unidade = cleanString($unidade);
    switch ($unidade) {
        case "unidade":
            $arr["minimo"] = 0;
            $arr["maximo"] = 120;
            $arr["incremento"] = 1;
            break;
        case "duzia":
            $arr["minimo"] = 0;
            $arr["maximo"] = 10;
            $arr["incremento"] = 0.5;
            break;
        case "kg":
            $arr["minimo"] = 0;
            $arr["maximo"] = 5;
            $arr["incremento"] = 0.25;
            break;
        default:
            $arr["minimo"] = 0;
            $arr["maximo"] = 0;
            $arr["incremento"] = 0;
    }
    return $arr;
}

//Remove caracteres especiais
function cleanString($text) {
    $dict = array(
        '/[áàâãªä]/'   =>   'a',
        '/[ÁÀÂÃÄ]/'    =>   'A',
        '/[ÍÌÎÏ]/'     =>   'I',
        '/[íìîï]/'     =>   'i',
        '/[éèêë]/'     =>   'e',
        '/[ÉÈÊË]/'     =>   'E',
        '/[óòôõºö]/'   =>   'o',
        '/[ÓÒÔÕÖ]/'    =>   'O',
        '/[úùûü]/'     =>   'u',
        '/[ÚÙÛÜ]/'     =>   'U',
        '/ç/'           =>   'c',
        '/Ç/'           =>   'C',
        '/ñ/'           =>   'n',
        '/Ñ/'           =>   'N',
        '/–/'           =>   '-', // UTF-8 hyphen to "normal" hyphen
        '/[’‘‹›‚]/'    =>   ' ', // Literally a single quote
        '/[“”«»„]/'    =>   ' ', // Double quote
        '/ /'           =>   ' ', // nonbreaking space (equiv. to 0x160)
    );
    return preg_replace(array_keys($dict), array_values($dict), $text);
}

function setLog($file,$msg,$sql) {
    $fp = fopen($file, 'a');
    if (session_status() == PHP_SESSION_NONE) {
        $msg = date("Y-m-d-H:i:s")." () ".$msg;
    } else {
        $msg = date("Y-m-d-H:i:s")." (".$_SESSION["login"].") ".$msg;
    }
    $msg .= " | ";
    $msg .= $sql;
    $msg = "\n\r".$msg;
    fwrite($fp,$msg);  
    fclose($fp);  
}

function formatCPF($cpf) {
    return substr($cpf,0,3).".".substr($cpf,3,3).".".substr($cpf,6,3)."-".substr($cpf,9,2);
}

function hashEmail($email) {
    $conta=0;
    for ($i = 0; $i < strlen($email); $i++) {
        $conta++;
        if ($conta == 3) {
            $email[$i] = "*";
            $conta=0;
        }
    }
    return $email;
}

function gerarCodigoSenha($length = 30) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
?>
