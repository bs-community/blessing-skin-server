use serde::Deserialize;

#[derive(Deserialize)]
pub struct JsonBody<T> {
    code: u8,
    data: Option<T>,
}

impl<T> JsonBody<T> {
    pub fn is_success(&self) -> bool {
        self.code == 0
    }

    pub fn data(self) -> Option<T> {
        self.data
    }
}
