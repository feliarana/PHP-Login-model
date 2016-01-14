<?php

require_once 'PDOConnection.php';

class Usuario_model {

    private  $id;
    private  $username;
    private  $password;
    private  $mail;
    private  $pdo;

    function __construct() {
        $this->pdo = new PDOConnection();
    }

    public function getId() {
        return ($this->id);
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getUsername() {
        return ($this->username);
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function getPassword() {
        return ($this->password);
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function setMail($mail) {
        $this->mail = $mail;
    }

    public function setDatos($user, $pass) {
        $error = array( );
        // USERNAME
        if(isset($user))
            $this->setUsername($user);
        else
            $error['username'] = "Este campo no debe estar vacio";
        
        // PASSWORD
        if(isset($pass))
            $this->setPassword($pass);
        else
            $error['password'] = "Este campo no debe estar vacio";

        //TODO: Mail verification
        
        return ($error);
    }

    public function esValidoNombreUsuario($username, $error) {
        if(preg_match('/^[a-z\d_]{2,20}$/i', $username))
            return ($error);
        else
            $error['username'] = "Este campo no cumple el formato solicitado";
        return (0);
    }

    public function esValidoPassword($password, $error) {
        if(preg_match('/^[a-z\d_]{6,30}$/i', $password))
            return ($error);
        else
            $error['password'] = "Este campo no cumple el formato solicitado";
        return (0);
    }

    public function esValidoMail($mail, $error) {
        if (filter_var($mail, FILTER_VALIDATE_EMAIL))
            return ($error);
        else
            $error['mail'] = "Este campo no cumple el formato solicitado";
        return (0);
    }


    public function checkLogin($user, $pass) {
        $errors= $this->setDatos($user, $pass);
        $this->esValidoNombreUsuario($this->getUsername(), $errors);
        $this->esValidoPassword($this->getPassword(), $errors);
        if (empty($errors)){
            if($this->login($user, $pass))                
                return 0;
            else
                $errors="Usuario o contrasenia incorrecto";
        }
        return ($errors);
    }

    public function login($username, $password) {
        $conn = $this->pdo->getConnection();
        $stmt = $conn->prepare("SELECT * FROM usuario WHERE username = :username AND password = :password");
        $stmt->bindValue(':username', $username, PDO::PARAM_STR);
        $stmt->bindValue(':password', $password, PDO::PARAM_STR);
        $stmt->execute();
        $this->pdo->closeConnection($conn); // Cerramos la conexion
        if($row = $stmt->fetch()) {
            $this->setId($row['id']);
            $this->setUsername($row['username']);
            $this->setPassword($row['password']);
            return (true);
        }
        else {
            return (false);
        }
    }

    public function save($error) {
        if(!$this->existeUsernameUsuario($this->getUsername())) {
            if(!$this->existeMailUsuario($this->getMail())) {
                $this->crearUsuario();
                if($this->getRol() == 'consulta') {
                    if($this->responsable->existeMailResponsable($this->getMail())) {
                        // Se trae el id del usuario recientemente creado para asignarlo a un responsable
                        $idUsuario = $this->obtenerIdUsuario($this->getUsername());
                        $this->responsable->asignarCuenta($this->getMail(), $idUsuario);
                    }
                    else {
                        $this->eliminarUsuario($idUsuario); // Como no existe un responsable con el mail ingresado para asignarle al usuario, la cuenta es eliminada
                        $error["mail"] = "No se pudo crear el usuario debido a que no se encontro un responsable con este mail para asignarle la cuenta";
                    }
                }
            }
            else {
                $error['mail'] = 'No se pudo crear el usuario debido a que este mail ya esta registrado en el sistema';
            }
        }
        else {
            $error['username'] = 'No se pudo crear el usuario debido a que este username ya esta registrado en el sistema';
        }
        return ($error);
    }

    public function edit($error) {
        if($this->existeUsuario($this->getId())) {
            // Si el username ingresado no existe se modifica el usuario, si ya existe en el sistema no se modifica el usuario y muestra un error (no tiene en cuenta el username del propio usuario a modificar)
            if(!$this->existeOtroUsernameUsuarioIgual($this->getId(), $this->getUsername())) {
                // Si el mail ingresado no existe se modifica el usuario, si ya existe en el sistema no se modifica el usuario y muestra un error (no tiene en cuenta el mail del propio usuario a modificar)
                if(!$this->existeOtroMailUsuarioIgual($this->getId(), $this->getMail())) {
                    if($this->getRol() == 'consulta') {
                        if($this->responsable->existeMailResponsable($this->getMail())) {
                            $this->modificarUsuario();
                            $this->responsable->asignarCuenta($this->getMail(), $this->getId());
                        }
                        else {
                            $error["mail"] = 'No se pudo modificar el usuario debido a que no se encontro un responsable con este mail para asignarle la cuenta';
                        }
                    }
                    else {
                        if($this->eraConsulta($this->getId()) and ($this->getRol() == 'administrador' or $this->getRol() == 'gestion')) {
                            $idResponsable = $this->responsable->obtenerIdResponsable($this->getMail());
                            $this->responsable->desenlazarCuenta($idResponsable);
                        }
                        $this->modificarUsuario();
                        if($this->getUsername() == $_SESSION['username']){
                            $_SESSION['rol'] = $this->getRol();
                        }
                    }
                }
                else {
                    $error["mail"] = 'No se pudo modificar el usuario debido a que este mail ya esta registrado en el sistema';
                }
            }
            else {
                $error["username"] = 'No se pudo modificar el usuario debido a que este username ya esta registrado en el sistema';
            }
        }
        else {
            $error["usuario"] = 'Error, el usuario que usted quiere modificar no existe en el sistema';
        }
        return ($error);
    }

    public function delete($error) {
        $this->setId($_POST['idUsuario']);
        if($this->getId() != $_SESSION['id']) {
            $this->eliminarUsuario();
        }
        else {
            $error = "Error, usted no puede autoeliminarse";
        }
        return ($error);
    }



    // Cuando se va a dar de alta un usuario, se verifica si existe en el sistema un usuario con el mismo username

    public function existeUsernameUsuario($username) {
        $conn = $this->pdo->getConnection();
        $stmt = $conn->prepare("SELECT username FROM usuario WHERE username = :username");
        $stmt->bindParam(":username", $username, PDO::PARAM_STR);
        $stmt->execute();
        $this->pdo->closeConnection($conn); // Cerramos la conexion
        if($stmt->rowCount() > 0) {
            return (true);
        }
        else {
            return (false);
        }
    }

    // Cuando se va a dar de alta un usuario, se verifica si existe en el sistema un usuario con el mismo mail

    public function existeMailUsuario($mail) {
        $conn = $this->pdo->getConnection();
        $stmt = $conn->prepare("SELECT mail FROM usuario WHERE mail = :mail");
        $stmt->bindParam(":mail", $mail, PDO::PARAM_STR);
        $stmt->execute();
        $this->pdo->closeConnection($conn); // Cerramos la conexion
        if($stmt->rowCount() > 0) {
            return (true);
        }
        else {
            return (false);
        }
    }

    // Cuando se va a modificar un usuario , se verifica si existe otro usuario con el username ingresado

    public function existeOtroUsernameUsuarioIgual($idUsuario, $username) {
        $conn = $this->pdo->getConnection();
        $stmt = $conn->prepare("SELECT username FROM usuario WHERE id <> :idUsuario AND username = :username");
        $stmt->bindParam(":idUsuario", $idUsuario, PDO::PARAM_STR);
        $stmt->bindParam(":username", $username, PDO::PARAM_STR);
        $stmt->execute();
        $this->pdo->closeConnection($conn); // Cerramos la conexion
        if($stmt->rowCount() > 0) {
            return (true);
        }
        else {
            return (false);
        }
    }

    // Cuando se va a modificar un usuario , se verifica si existe otro usuario con el mail ingresado

    public function existeOtroMailUsuarioIgual($idUsuario, $mail) {
        $conn = $this->pdo->getConnection();
        $stmt = $conn->prepare("SELECT mail FROM usuario WHERE id <> :idUsuario AND mail = :mail");
        $stmt->bindParam(":idUsuario", $idUsuario, PDO::PARAM_STR);
        $stmt->bindParam(":mail", $mail, PDO::PARAM_STR);
        $stmt->execute();
        $this->pdo->closeConnection($conn); // Cerramos la conexion
        if($stmt->rowCount() > 0) {
            return (true);
        }
        else {
            return (false);
        }
    }

    public function existeUsuario($idUsuario) {
        $conn = $this->pdo->getConnection();
        $stmt = $conn->prepare("SELECT id FROM usuario WHERE id = :idUsuario");
        $stmt->bindParam(":idUsuario", $idUsuario, PDO::PARAM_STR);
        $stmt->execute();
        $this->pdo->closeConnection($conn); // Cerramos la conexion
        if($stmt->rowCount() > 0) {
            return (true);
        }
        else {
            return (false);
        }
    }

    public function obtenerUsuario($idUsuario) {
        $conn = $this->pdo->getConnection();
        $stmt = $conn->prepare("SELECT * FROM usuario WHERE id = :idUsuario");
        $stmt->bindParam(":idUsuario", $idUsuario, PDO::PARAM_STR);
        $stmt->execute();
        $this->pdo->closeConnection($conn); // Cerramos la conexion
        if($stmt->rowCount() > 0) {
            return ($stmt->fetch());
        }
        else {
            return (false);
        }
    }

    public function obtenerIdUsuario($username) {
        $conn = $this->pdo->getConnection();
        $stmt = $conn->prepare("SELECT id FROM usuario WHERE username = :username");
        $stmt->bindParam(":username", $username, PDO::PARAM_STR);
        $stmt->execute();
        $this->pdo->closeConnection($conn); // Cerramos la conexion
        if($stmt->rowCount() > 0) {
            return ($stmt->fetchColumn()); // Lo que hace fetchColumn() es devolver una única columna de la siguiente fila de un conjunto de resultados. Por ejemplos si se tienen 2 filas y se lo usa la primera vez devuelve un dato de la primera fila y si se lo usa otra vez devuelve un dato de la segunda fila. Si se le ingresa un parametro fetchColumn(1) lo que hace es devolver la segunda columna de la siguiente fila. En este caso devuelve la primera columna de la primera fila porque se usa una vez y tenemos una sola fila
        }
        else {
            return (false);
        }
    }

    public function crearUsuario() {
        $conn = $this->pdo->getConnection();
        $stmt = $conn->prepare("INSERT INTO usuario (username, password, habilitado, rol, mail)
            VALUES (
                :username,
                :password,
                :habilitado,
                :rol,
                :mail)");
        $stmt->bindValue(":username", $this->getUsername(), PDO::PARAM_STR);
        $stmt->bindValue(":password", $this->getPassword(), PDO::PARAM_STR);
        $stmt->bindValue(":habilitado", $this->getHabilitado(), PDO::PARAM_STR);
        $stmt->bindValue(":rol", $this->getRol(), PDO::PARAM_STR);
        $stmt->bindValue(":mail", $this->getMail(), PDO::PARAM_STR);
        $stmt->execute();
        $this->pdo->closeConnection($conn); // Cerramos la conexion
    }

    public function modificarUsuario() {
        $conn = $this->pdo->getConnection();
        $stmt = $conn->prepare("UPDATE usuario
            SET
                username = :username,
                password = :password,
                habilitado = :habilitado,
                rol = :rol,
                mail = :mail
            WHERE id = :idUsuario");
        $stmt->bindValue(":idUsuario", $this->getId(), PDO::PARAM_STR);
        $stmt->bindValue(":username", $this->getUsername(), PDO::PARAM_STR);
        $stmt->bindValue(":password", $this->getPassword(), PDO::PARAM_STR);
        $stmt->bindValue(":habilitado", $this->getHabilitado(), PDO::PARAM_STR);
        $stmt->bindValue(":rol", $this->getRol(), PDO::PARAM_STR);
        $stmt->bindValue(":mail", $this->getMail(), PDO::PARAM_STR);
        $stmt->execute();
        $this->pdo->closeConnection($conn); // Cerramos la conexion
    }

    public function eliminarUsuario() {
        $conn = $this->pdo->getConnection();
        $stmt = $conn->prepare("DELETE FROM usuario WHERE id = :idUsuario");
        $stmt->bindValue(":idUsuario", $this->getId(), PDO::PARAM_STR);
        $stmt->execute();
        $this->pdo->closeConnection($conn); // Cerramos la conexion
    }

}

?>