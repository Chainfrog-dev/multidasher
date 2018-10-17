import { Component, OnInit } from '@angular/core';
import { FormBuilder, Validators } from '@angular/forms';
import { DataService } from '../data-feeds.service';
import { ActivatedRoute, Router, NavigationEnd } from '@angular/router';    

@Component({
  selector: 'app-create-address',
  templateUrl: './create-address.component.html',
  styleUrls: ['./create-address.component.scss']
})

export class CreateAddressComponent implements OnInit {
  permissions : String[] = ['activate','admin','connect','create','issue','mine','receive','send'];
  blockchainId : String;
  addressForm = this.fb.group({
    title: [null, Validators.required],
    permissions: this.fb.array(this.permissions)
  });

  constructor(
    private fb: FormBuilder,
    private route: ActivatedRoute,
    private dataService : DataService
  ) {
    this.blockchainId = this.route.snapshot.params['blockchainId'];
  }

  ngOnInit() {
  }

  async submitForm() {
    const submittedPermissionsArray : String[] = [];
    for(let key in this.addressForm.value.permissions) {
      if(this.addressForm.value.permissions[key] !== false){
        submittedPermissionsArray.push(this.permissions[key]);
      }
    }
    const submittedPermissions = submittedPermissionsArray.join();
    const result = await this.dataService.addAddress(this.blockchainId,this.addressForm.value.title,submittedPermissions).toPromise();
    if(result['status'] == 1){
      alert('congrats, wallet address '+result['data']['message']['result']+' has been created');
    }
  }
}
