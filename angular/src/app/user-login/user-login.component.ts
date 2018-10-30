import { Component, OnInit } from '@angular/core';
import { AuthService } from '../auth.service';
import { ActivatedRoute, Router, NavigationEnd } from '@angular/router';
import { FormBuilder, FormGroup, Validators, FormControl } from '@angular/forms';

@Component({
  selector: 'app-user-login',
  templateUrl: './user-login.component.html',
  styleUrls: ['./user-login.component.scss']
})
export class UserLoginComponent implements OnInit {
  userRegistration: FormGroup;
  userLogin: FormGroup;
  resetPasswordForm: FormGroup;

  constructor(
    private authService: AuthService, 
    private _formBuilder: FormBuilder,
    private _router : Router
  ) {}

  ngOnInit() {
    // User Login form
    this.userLogin = this._formBuilder.group({ 
      'email' : [null, Validators.required],
      'password': ['', Validators.required]
    }); 
  }

  async loginEmailPassword(username,password){
    const login = await this.authService.loginDrupal(username,password).toPromise();
    console.log(login);
  }

}