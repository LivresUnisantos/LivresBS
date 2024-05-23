<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];

    if ($action == 'add') {
        $productId = $_POST['product_id'];
        $stmt = $pdo->prepare('INSERT INTO cart (product_id, quantity) VALUES (?, 1) ON DUPLICATE KEY UPDATE quantity = quantity + 1');
        $stmt->execute([$productId]);
    } elseif ($action == 'update') {
        foreach ($_POST['cart_id'] as $index => $cartId) {
            $quantity = $_POST['quantity'][$index];
            $stmt = $pdo->prepare('UPDATE cart SET quantity = ? WHERE id = ?');
            $stmt->execute([$quantity, $cartId]);
        }
    } elseif ($action == 'checkout') {
        // Aqui você pode processar o pedido e limpar o carrinho
        $stmt = $pdo->query('DELETE FROM cart');
        // Exibir mensagem de sucesso ou redirecionar para uma página de confirmação
        echo "Pedido finalizado com sucesso!";
    }
}

header('Location: index.php');
