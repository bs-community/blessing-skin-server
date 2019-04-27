use crate::auth::login;
use crate::types::JsonBody;
use serde::Deserialize;
use serde_json::json;

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
