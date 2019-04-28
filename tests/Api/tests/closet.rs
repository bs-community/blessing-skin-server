use crate::auth::login;
use crate::types::JsonBody;
use rusqlite::{params, Connection};
use serde::Deserialize;
use serde_json::json;
use std::env;

#[derive(Deserialize)]
struct Closet {
    pub category: String,
    pub total_pages: usize,
    pub items: Vec<ClosetItem>,
}

#[derive(Deserialize)]
struct ClosetItem {
    pub tid: u32,
    pub name: String,
    pub r#type: String,
    pub size: u32,
    pub hash: String,
    pub uploader: u32,
    pub public: bool,
}

#[test]
fn fetch_closet_info() {
    let token = login();
    let client = reqwest::Client::new();

    let body = client
        .get("http://127.0.0.1:32123/api/closet")
        .header("Authorization", token.clone())
        .send()
        .unwrap()
        .json::<JsonBody<Closet>>()
        .unwrap();
    assert!(body.is_success());
    let closet = body.data().unwrap();
    assert_eq!(closet.category, "skin");
    assert_eq!(closet.total_pages, 0);
    assert_eq!(closet.items.len(), 0);

    let body = client
        .get("http://127.0.0.1:32123/api/closet")
        .header("Authorization", token)
        .json(&json!({"category": "cape"}))
        .send()
        .unwrap()
        .json::<JsonBody<Closet>>()
        .unwrap();
    assert!(body.is_success());
    let closet = body.data().unwrap();
    assert_eq!(closet.category, "cape");
    assert_eq!(closet.total_pages, 0);
    assert_eq!(closet.items.len(), 0);
}

#[test]
fn insert_to_closet() {
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

    let token = login();
    let client = reqwest::Client::new();

    let body = client
        .post("http://127.0.0.1:32123/api/closet")
        .header("Authorization", token.clone())
        .json(&json!({"tid": 1, "name": "my-first-texture"}))
        .send()
        .unwrap()
        .json::<JsonBody<()>>()
        .unwrap();
    assert!(body.is_success());

    let body = client
        .get("http://127.0.0.1:32123/api/closet")
        .header("Authorization", token)
        .send()
        .unwrap()
        .json::<JsonBody<Closet>>()
        .unwrap();
    assert!(body.is_success());
    let closet = body.data().unwrap();
    assert_eq!(closet.total_pages, 1);
    assert_eq!(closet.items.len(), 1);
    let item = closet.items.get(0).unwrap();
    assert_eq!(item.tid, 1);
    assert_eq!(item.name, "my-first-texture");
    assert_eq!(item.r#type, "steve");
    assert_eq!(item.size, 1);
    assert_eq!(item.hash, "abc");
    assert_eq!(item.uploader, 1);
    assert!(item.public);
}

#[test]
fn modify_name() {
    let token = login();
    let client = reqwest::Client::new();

    let body = client
        .put("http://127.0.0.1:32123/api/closet/1")
        .header("Authorization", token.clone())
        .json(&json!({"name": "renamed"}))
        .send()
        .unwrap()
        .json::<JsonBody<()>>()
        .unwrap();
    assert!(body.is_success());

    let body = client
        .get("http://127.0.0.1:32123/api/closet")
        .header("Authorization", token)
        .send()
        .unwrap()
        .json::<JsonBody<Closet>>()
        .unwrap();
    assert!(body.is_success());
    let closet = body.data().unwrap();
    let item = closet.items.get(0).unwrap();
    assert_eq!(item.tid, 1);
    assert_eq!(item.name, "renamed");
}

#[test]
fn remove_texture() {
    let token = login();
    let client = reqwest::Client::new();

    let body = client
        .delete("http://127.0.0.1:32123/api/closet/1")
        .header("Authorization", token.clone())
        .send()
        .unwrap()
        .json::<JsonBody<()>>()
        .unwrap();
    assert!(body.is_success());

    let body = client
        .get("http://127.0.0.1:32123/api/closet")
        .header("Authorization", token)
        .send()
        .unwrap()
        .json::<JsonBody<Closet>>()
        .unwrap();
    assert!(body.is_success());
    let closet = body.data().unwrap();
    assert_eq!(closet.items.len(), 0);
}
