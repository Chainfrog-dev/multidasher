//THIS FILE CONNECTS TO THE PYTHON API 
// AND OPENS AND SAVES THE SESSION IN THE ANGULAR APPLICATION.
import { Injectable }    from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../environments/environment';

class promise {};

export interface Authentification {
  username: string;
  password: string;
  crsfToken: string;
  logoutToken: string;
}

@Injectable() 

export class AuthService {
  // Load the API based on the local environment settings
  host: string = environment.host;
  authentification: Authentification;

  constructor(
    private http: HttpClient
  ) {}

  loginDrupal(username, password) : Observable<promise> {

    const payload = new FormData();
    payload.set('name', username);
    payload.set('pass', password);
    payload.set('form_id', 'user_login_form');

    const data = {
      'name' : username,
      'pass' : password,
      'form_id' : 'user_login_form'
    }

    return this.http.post(
      this.host+'/user/login?_format=json',
      data,
      {headers : new HttpHeaders({
        'Accept' : 'application/json',
        'Content-Type' : 'application/x-www-form-urlencoded'
      })} 
    );
  }

  setAuthentification(crsfToken, username, pass){
    this.authentification.crsfToken = crsfToken;
    this.authentification.username = username;
    this.authentification.password = pass;
  }

  getAuthentification() {
    return this.authentification;
  }

}
