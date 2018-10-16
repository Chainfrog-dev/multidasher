import { Component, OnInit } from '@angular/core';
import { FormBuilder, Validators } from '@angular/forms';

@Component({
  selector: 'app-create-address',
  templateUrl: './create-address.component.html',
  styleUrls: ['./create-address.component.scss']
})


export class CreateAddressComponent implements OnInit {
  permissions : String[] = ['activate','admin','connect','create','issue','mine','receive','send'];
  
  addressForm = this.fb.group({
    title: [null, Validators.required],
    permissions: [, Validators.required]
  });

  constructor(private fb: FormBuilder) {

  }

  ngOnInit() {

  }

  onSubmit() {
    alert('Thanks!');
  }
}
