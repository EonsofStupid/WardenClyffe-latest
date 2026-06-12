// Warden · identity — auth types (mirror the Go identity context).
export interface Operator {
  username: string;
  role: string;
}

export interface LoginResult {
  token: string;
  operator: Operator;
}
