let url, headers, body;

header = {
    "content-type" : "application/json",
    "accept" : "application/json"
}

/** Register user *****************************************************************************************************/

url = "/en/api/v1/user/register";
body = {
    "business": "f9c83aa9-d5d9-4232-8c72-8d284418a180",
    "name" : "User Name",
    "email" : "mail@mail.com",
    "password" : "Password",
    "password_confirmation" : "Password"
}

/** Login user  *******************************************************************************************************/
url = "/en/api/v1/user/login";
body = {
    "email": "mail@mail.com",
    "password" : "Password"
}

/** Get data of user **************************************************************************************************/
url = "/api/v1/user/{id}";
header = {
    "content-type" : "application/json",
    "accept" : "application/json",
    "Authorization" : "Bearer WeyJ0eXAiO..."
}

/** Filter objects from relations **/
url = 'https://weagencylocal.com/en/api/v1/object/all?object_type=ciudad&sort=created_at&direction=desc&condition={"type":"OR","expressions":[{"type":"=","left":"ciudad_pais","right":"112"}]}'


/** Obtener disponibilidad de relaciones para objeto **/
url = 'https://weagencylocal.com/en/api/v1/object/available?existence=noticia_agencia.rl_ag_pais&object_type=pais'

/** Obtener los comentarios de un objeto **/

url = 'https://weagencylocal.com/en/api/v1/comment/all?object=264'
