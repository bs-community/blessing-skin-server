use crate::auth::login;
use crate::types::JsonBody;
use serde::Deserialize;

#[derive(Deserialize)]
struct User {
    pub uid: u32,
    pub email: String,
    pub nickname: String,
    pub avatar: u32,
    pub score: u32,
}

#[test]
fn fetch_user_info() {
    let client = reqwest::Client::new();
    let body = client
        .get("http://127.0.0.1:32123/api/user")
        .header("Authorization", login())
        .send()
        .unwrap()
        .json::<JsonBody<User>>()
        .unwrap();
    assert!(body.is_success());

    let user = body.data().unwrap();
    assert_eq!(user.uid, 1);
    assert_eq!(user.email, String::from("ibara.mayaka@api.test"));
    assert_eq!(user.nickname, String::from("hyouka"));
    assert_eq!(user.avatar, 0);
    assert_eq!(user.score, 1000);
}
