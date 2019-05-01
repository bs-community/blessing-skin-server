use serde::Deserialize;

#[derive(Deserialize)]
struct RootInfo {
    blessing_skin: String,
    spec: u8,
    copyright: String,
    site_name: String,
}

#[test]
fn api_root() {
    let body = reqwest::get("http://127.0.0.1:32123/api")
        .unwrap()
        .json::<RootInfo>()
        .unwrap();
    assert!(body.blessing_skin.starts_with("4"));
    assert_eq!(body.spec, 0);
    assert_eq!(body.copyright, "Powered with ❤ by Blessing Skin Server.");
    assert_eq!(body.site_name, "Blessing Skin");
}
