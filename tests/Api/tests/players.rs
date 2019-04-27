use crate::auth::login;
use crate::types::JsonBody;
use rusqlite::{params, Connection};
use serde::Deserialize;
use serde_json::json;
use std::env;

#[derive(Deserialize)]
struct Player {
    pub pid: u32,
    pub name: String,
    pub tid_skin: u32,
    pub tid_cape: u32,
}

#[test]
fn create_player() {
    let client = reqwest::Client::new();
    let body = client
        .post("http://127.0.0.1:32123/api/players")
        .header("Authorization", login())
        .json(&json!({"name": "kotenbu_member"}))
        .send()
        .unwrap()
        .json::<JsonBody<Player>>()
        .unwrap();
    assert!(body.is_success());
}

#[test]
fn get_all_players() {
    let client = reqwest::Client::new();
    let body = client
        .get("http://127.0.0.1:32123/api/players")
        .header("Authorization", login())
        .send()
        .unwrap()
        .json::<JsonBody<Vec<Player>>>()
        .unwrap();
    assert!(body.is_success());
    let players = body.data().unwrap();
    assert_eq!(players.len(), 1);

    let player = players.get(0).unwrap();
    assert_eq!(player.pid, 1);
    assert_eq!(player.name, String::from("kotenbu_member"));
    assert_eq!(player.tid_skin, 0);
    assert_eq!(player.tid_cape, 0);
}

#[test]
fn modify_player_name() {
    let client = reqwest::Client::new();
    let body = client
        .put("http://127.0.0.1:32123/api/players/1/name")
        .header("Authorization", login())
        .json(&json!({"name": "kotenbu"}))
        .send()
        .unwrap()
        .json::<JsonBody<Player>>()
        .unwrap();
    assert!(body.is_success());

    let player = body.data().unwrap();
    assert_eq!(player.name, String::from("kotenbu"));
}

#[test]
fn modify_textures() {
    let conn = Connection::open(env::var("DB_DATABASE").unwrap()).unwrap();
    conn.execute(
        "INSERT INTO textures (name, type, hash, size, uploader, public, upload_at)
            VALUES (?1, ?2, ?3, ?4, ?5, ?6, ?7)",
        params!["steve", "steve", "abc", 1, 1, 1, "2019-01-01 00:00:00"],
    )
    .unwrap();
    conn.execute(
        "INSERT INTO textures (name, type, hash, size, uploader, public, upload_at)
            VALUES (?1, ?2, ?3, ?4, ?5, ?6, ?7)",
        params!["cape", "cape", "def", 1, 1, 1, "2019-01-01 00:00:00"],
    )
    .unwrap();

    let client = reqwest::Client::new();
    let body = client
        .put("http://127.0.0.1:32123/api/players/1/textures")
        .header("Authorization", login())
        .json(&json!({"skin": 1}))
        .send()
        .unwrap()
        .json::<JsonBody<Player>>()
        .unwrap();
    assert!(body.is_success());
    let player = body.data().unwrap();
    assert_eq!(player.tid_skin, 1);
    assert_eq!(player.tid_cape, 0);

    let body = client
        .put("http://127.0.0.1:32123/api/players/1/textures")
        .header("Authorization", login())
        .json(&json!({"cape": 2}))
        .send()
        .unwrap()
        .json::<JsonBody<Player>>()
        .unwrap();
    assert!(body.is_success());
    let player = body.data().unwrap();
    assert_eq!(player.tid_skin, 1);
    assert_eq!(player.tid_cape, 2);
}

#[test]
fn modify_textures_reset() {
    let client = reqwest::Client::new();
    let body = client
        .delete("http://127.0.0.1:32123/api/players/1/textures")
        .header("Authorization", login())
        .json(&json!({"cape": 1}))
        .send()
        .unwrap()
        .json::<JsonBody<Player>>()
        .unwrap();
    assert!(body.is_success());
    let player = body.data().unwrap();
    assert_eq!(player.tid_skin, 1);
    assert_eq!(player.tid_cape, 0);

    let body = client
        .delete("http://127.0.0.1:32123/api/players/1/textures")
        .header("Authorization", login())
        .json(&json!({"type": ["skin", "cape"]}))
        .send()
        .unwrap()
        .json::<JsonBody<Player>>()
        .unwrap();
    assert!(body.is_success());
    let player = body.data().unwrap();
    assert_eq!(player.tid_skin, 0);
    assert_eq!(player.tid_cape, 0);
}

#[test]
fn remove_player() {
    let client = reqwest::Client::new();
    let body = client
        .delete("http://127.0.0.1:32123/api/players/1")
        .header("Authorization", login())
        .send()
        .unwrap()
        .json::<JsonBody<Player>>()
        .unwrap();
    assert!(body.is_success());

    let body = client
        .get("http://127.0.0.1:32123/api/players")
        .header("Authorization", login())
        .send()
        .unwrap()
        .json::<JsonBody<Vec<Player>>>()
        .unwrap();
    assert_eq!(body.data().unwrap().len(), 0);
}
