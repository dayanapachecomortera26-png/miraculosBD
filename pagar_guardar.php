<?php
$conexion = new mysqli("localhost", "root", "", "miraculos_cafe");
if($conexion->connect_error){ die("Error: ".$conexion->connect_error); }

if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['accion']) && $_POST['accion']=='guardar'){
  $nombre = $conexion->real_escape_string($_POST['nombre']);
  $pago = $conexion->real_escape_string($_POST['pago']);
  $direccion = $conexion->real_escape_string($_POST['direccion']);
  $total = floatval($_POST['total']);
  $carrito = json_decode($_POST['carrito_json'], true);
  
  $conexion->query("INSERT INTO pedidos (nombre_cliente, metodo_pago, direccion, total) VALUES ('$nombre','$pago','$direccion',$total)");
  $pedido_id = $conexion->insert_id;
  
  foreach($carrito as $item){
    $sub = $item['precio'] * $item['cantidad'];
    $conexion->query("INSERT INTO pedido_detalle (pedido_id, producto_id, cantidad, subtotal) VALUES ($pedido_id, {$item['id']}, {$item['cantidad']}, $sub)");
  }
  echo "✅ Pedido #$pedido_id guardado - Total $$total - Gracias $nombre!";
  exit;
}

// SIN usar emoji, para que no de Warning
$productos = $conexion->query("SELECT id, nombre, precio, categoria FROM productos ORDER BY categoria");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Miraculous - Comprar y Pagar</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Arial,sans-serif}
body{background:#fff5f7}
header{background:linear-gradient(90deg,#ff6b3d,#ff8a4c);padding:15px 20px;display:flex;justify-content:space-between;align-items:center;border-radius:0 0 20px 20px;color:white;font-weight:800;font-size:22px}
.main{display:grid;grid-template-columns:1.2fr .8fr;gap:15px;padding:15px;max-width:1300px;margin:auto}
@media(max-width:900px){.main{grid-template-columns:1fr}}
.card{background:white;border-radius:20px;border:2px solid #ffb6c1;overflow:hidden}
.card-header{background:linear-gradient(90deg,#f7798a,#f96b8d);color:white;padding:15px;text-align:center}
.card-body{padding:12px;max-height:80vh;overflow-y:auto}
.cat{background:#ffeaa7;padding:5px 12px;border-radius:15px;font-weight:700;font-size:12px;margin:12px 0 6px;display:inline-block}
.prod{display:flex;justify-content:space-between;align-items:center;background:#fffafb;border:1px solid #ffe0e6;border-radius:12px;padding:10px;margin-bottom:8px}
.btn-add{background:#ffc93c;border:0;padding:6px 14px;border-radius:20px;font-weight:700;cursor:pointer;color:white}
.cart-item{display:flex;justify-content:space-between;background:#fff6f7;padding:8px;border-radius:10px;margin-bottom:6px;font-size:13px}
.total{background:#fff0f3;padding:12px;text-align:center;font-weight:800;border-radius:10px;margin:10px 0}
input,select,textarea{width:100%;padding:10px;border-radius:10px;border:1.5px solid #ffd0d8;margin-top:4px}
.btn-save{width:100%;background:#8ed18e;padding:12px;border:0;border-radius:25px;font-weight:800;cursor:pointer;margin-top:10px}
.btn-save:disabled{background:#c8e6c8}
</style>
</head>
<body>
<header><span>⭐ Miraculous 💙</span><span style="background:#fff8dc;color:#ff6b3d;padding:6px 14px;border-radius:20px;font-size:14px">🛒 Comprar y Pagar</span></header>
<div class="main">
<div class="card">
<div class="card-header"><h2>📋 Productos Disponibles</h2><p style="font-size:12px">Agrega lo que desees ✨</p></div>
<div class="card-body">
<?php
$cat_actual="";
while($p=$productos->fetch_assoc()){
  if($cat_actual!=$p['categoria']){ $cat_actual=$p['categoria']; echo "<div class='cat'>$cat_actual</div>"; }
  // YA SIN EMOJI - ARREGLADO LINEA 65 y 66
  echo "<div class='prod'>
    <div><b style='font-size:13px'>{$p['nombre']}</b><br><span style='color:#8e4a5d;font-weight:700'>\${$p['precio']}</span></div>
    <button class='btn-add' onclick=\"add({$p['id']},'".addslashes($p['nombre'])."',{$p['precio']})\">+ Agregar</button>
  </div>";
}
?>
</div>
</div>
<div class="card">
<div class="card-header"><h2>🛒 Tu Pedido</h2><p style="font-size:12px">Completa y guarda 💕</p></div>
<div class="card-body">
<div id="cart"><p style="text-align:center;color:#a77;padding:20px">Tu carrito está vacío 😊</p></div>
<div class="total" id="totalBox">Total: $0.00</div>
<div style="font-weight:700;margin:10px 0">📝 Datos para Guardar</div>
<input type="text" id="nombre" placeholder="👤 Nombre Completo">
<select id="pago" style="margin-top:10px"><option value="">💳 Método de Pago</option><option>Efectivo</option><option>Tarjeta</option><option>Transferencia</option></select>
<textarea id="direccion" rows="3" placeholder="📍 Dirección de Entrega" style="margin-top:10px"></textarea>
<button class="btn-save" id="btnGuardar" disabled>💾 GUARDAR COMPRA — $0.00</button>
</div>
</div>
</div>
<script>
let carrito={};
function add(id,nombre,precio){
 if(!carrito[id]) carrito[id]={id,nombre,precio,cantidad:0};
 carrito[id].cantidad++; render();
}
function quitar(id){ carrito[id].cantidad--; if(carrito[id].cantidad<=0) delete carrito[id]; render(); }
function render(){
 let html='', total=0;
 for(let id in carrito){
  let it=carrito[id]; let sub=it.precio*it.cantidad; total+=sub;
  html+=`<div class="cart-item"><span>${it.nombre} x${it.cantidad}</span><span>$${sub.toFixed(2)} <button onclick="quitar(${id})">-</button> <button onclick="add(${it.id},'${it.nombre}',${it.precio})">+</button></span></div>`;
 }
 if(!html) html='<p style="text-align:center;color:#a77;padding:20px">Tu carrito está vacío 😊</p>';
 document.getElementById('cart').innerHTML=html;
 document.getElementById('totalBox').textContent='Total: $'+total.toFixed(2);
 document.getElementById('btnGuardar').textContent='💾 GUARDAR COMPRA — $'+total.toFixed(2);
 document.getElementById('btnGuardar').dataset.total=total;
 check();
}
function check(){
 let ok = document.getElementById('nombre').value.trim() && document.getElementById('pago').value && document.getElementById('direccion').value.trim() && Object.keys(carrito).length>0;
 document.getElementById('btnGuardar').disabled=!ok;
}
document.getElementById('nombre').addEventListener('input',check);
document.getElementById('pago').addEventListener('change',check);
document.getElementById('direccion').addEventListener('input',check);
document.getElementById('btnGuardar').onclick = function(){
 let total = this.dataset.total;
 let datos = new FormData();
 datos.append('accion','guardar');
 datos.append('nombre',document.getElementById('nombre').value);
 datos.append('pago',document.getElementById('pago').value);
 datos.append('direccion',document.getElementById('direccion').value);
 datos.append('total',total);
 datos.append('carrito_json',JSON.stringify(carrito));
 fetch('',{method:'POST',body:datos})
 .then(r=>r.text())
 .then(res=>{ alert(res); carrito={}; render(); document.getElementById('nombre').value=''; document.getElementById('pago').value=''; document.getElementById('direccion').value=''; });
};
</script>
</body>
</html>