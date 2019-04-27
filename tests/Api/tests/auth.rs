use serde::Deserialize;
use serde_json::json;

#[derive(Deserialize)]
struct Token {
    token: String,
}

pub fn login() -> String {
    let client = reqwest::Client::new();
    let mut response = client
        .post("http://127.0.0.1:32123/api/auth/login")
        .json(&json!({
            "email": "ibara.mayaka@api.test",
            "password": "12345678"
        }))
        .send()
        .unwrap();
    let Token { token } = response.json().unwrap();
    format!("Bearer {}", token)
}

#[test]
fn successful_login() {
    assert_ne!(login(), "Bearer ".to_string());
}

#[test]
fn failed_login() {
    let client = reqwest::Client::new();
    let mut response = client
        .post("http://127.0.0.1:32123/api/auth/login")
        .json(&json!({
            "email": "ibara.mayaka@api.test",
            "password": "wrong-password"
        }))
        .send()
        .unwrap();
    let Token { token } = response.json().unwrap();
    assert_eq!(token, "".to_string());
}

#[test]
fn logout() {
    let token = login();
    let client = reqwest::Client::new();
    let response = client
        .post("http://127.0.0.1:32123/api/auth/logout")
        .header("Authorization", token)
        .send()
        .unwrap();
    assert_eq!(response.status(), reqwest::StatusCode::NO_CONTENT);
}

#[test]
fn refresh_token() {
    let token = login();
    let client = reqwest::Client::new();
    let mut response = client
        .post("http://127.0.0.1:32123/api/auth/refresh")
        .header("Authorization", token.clone())
        .send()
        .unwrap();
    let Token { token: new_token } = response.json().unwrap();
    assert_ne!(token, new_token);
}
