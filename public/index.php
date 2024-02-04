<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Selective\BasePath\BasePathMiddleware;
use Slim\Factory\AppFactory;



require_once __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();


// Parse json, form data and xml
$app->addBodyParsingMiddleware();

$app->addRoutingMiddleware();

$app->add(new BasePathMiddleware($app));

$app->addErrorMiddleware(true, true, true);


$app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
    $name = $args['name'];
    $response->getBody()->write("Hello, $name");
    return $response;
})->setName('root');

$app->get('/probando', function (Request $request, Response $response) {
    $db= new mysqli('localhost', 'root','','curso_angular17');
    $response->getBody()->write("Probando la SLIM KLK");
    var_dump($db);
    return $response;
});
/*
$app->post('/products', function (Request $request,Response $response,$app) {
    $json = $app->request->post('json');    
    $data = json_decode($json, true);
    
    var_dump($json);
    var_dump($data);
    
});
*/
//LIST ALL PRODUCTS
$app->get('/products', function (Request $request, Response $response) {
    $db= new mysqli('localhost', 'root','','curso_angular17');

    $sql = "SELECT *FROM productos ORDER BY id DESC;";

    $result = $db->query($sql);

    if ($result) {
        $productos = array();

        // Obtén cada fila como un array asociativo
        while ($producto = $result->fetch_assoc()) {
            // Agrega la fila al array de resultados
            $productos[] = $producto;
        }
        $response->getBody()->write(json_encode($productos));
        
    } else {
        // Maneja errores en la consulta
        $response->getBody()->write("Error en la consulta: " . $db->error);
        $response = $response->withStatus(500); // Código de estado 500 para error interno del servidor
    }

    // Cierra la conexión y la declaración
    $db->close();
    
    return $response;
});
//RETURN A SINGLE PRODUCT
$app->get('/products/{id}', function (Request $request, Response $response, array $args) {

    $db= new mysqli('localhost', 'root','','curso_angular17');
    $id = $args['id'];
    $sql = "SELECT *FROM productos WHERE id=".$id;

    $query = $db->query($sql);
    
    $result = array(
        "status" => "error",
        "code"=> 404,
        "message"=> "Producto no enco   ntrado"
    );
    if ($query->num_rows == 1) {
        $producto = $query->fetch_assoc();
        $result = array(
            "status" => "success",
            "code"=> 200,
            "data"=> $producto
        );
    }

    $response->getBody()->write(json_encode($result));


    $db->close();
    return $response;
});

//DELETE PRODUCT

//UPDATE PRODUCT

//UPLOAD AN IMAGE TO A PRODUCT

//SAVE PRODUCTS
$app->post('/products', function (Request $request, Response $response): Response {

    // Obtén el cuerpo de la solicitud como JSON
    $data = $request->getParsedBody();
    $html = var_export($data, true);
    $response->getBody()->write($html);
    $data2 = implode('',$data);
    $decode = json_decode($data2, true);

    // Verifica si la decodificación fue exitosa
    if ($data === null) {
        // Manejar error de decodificación JSON
        $response->getBody()->write("Error al decodificar JSON");
        return $response->withStatus(400); // Código de estado 400 para solicitud incorrecta
    }

    //if values are empty will be equal null
    if(!isset($decode['nombre'])) {
        $decode['nombre']= null;
    }

    if(!isset($decode['description'])) {
        $decode['description']= null;
    }

    if(!isset($decode['precio'])) {
        $decode['precio']= null;
    }

    if(!isset($decode['imagen'])) {
        $decode['imagen']= null;
    }
    
    // Extrae los datos necesarios
    $nombre = $decode['nombre'];
    $description = $decode['description'];
    $precio = $decode['precio'];
    $imagen = $decode['imagen'];

    // Crea una conexión a la base de datos
    $db = new mysqli('localhost', 'root', '', 'curso_angular17');

    // Verifica si hay errores en la conexión
    if ($db->connect_error) {
        die("Error de conexión: " . $db->connect_error);
    }
   
    // Prepara la consulta SQL
    $sql = "INSERT INTO productos (nombre, description, precio, imagen) VALUES (?, ?, ?, ?)";

    // Prepara la declaración
    $stmt = $db->prepare($sql);

    // Vincula los parámetros
    $stmt->bind_param("ssss", $nombre, $description, $precio, $imagen);
    var_dump($sql);
    // Ejecuta la consulta
    $result = $stmt->execute();

    // Verifica si la inserción fue exitosa
    if ($result) {
        $response->getBody()->write("Inserción exitosa");
    } else {
        $response->getBody()->write("Error en la inserción: " . $stmt->error);
    }

    // Cierra la conexión y la declaración
    $stmt->close();
    $db->close();

    return $response;
   
});

try {
    $app->run();     
} catch (Exception $e) {    
  // We display a error message
  die( json_encode(array("status" => "failed", "message" => "This action is not allowed"))); 
}