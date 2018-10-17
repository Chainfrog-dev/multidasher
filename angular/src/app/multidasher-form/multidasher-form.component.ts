import { Component, ViewChild } from '@angular/core';
import { FormBuilder, Validators } from '@angular/forms';

@Component({
  selector: 'app-multidasher-form',
  templateUrl: './multidasher-form.component.html',
    styleUrls: ['./multidasher-form.component.scss']
})
export class MultidasherFormComponent {
  addressForm = this.fb.group({
    company: null,
    firstName: [null, Validators.required],
    lastName: [null, Validators.required],
    address: [null, Validators.required],
    address2: null,
    city: [null, Validators.required],
    state: [null, Validators.required],
    postalCode: [null, Validators.required, Validators.minLength(5), Validators.maxLength(5)],
    shipping: ['free', Validators.required]
  });

  constructor(private fb: FormBuilder) {

  }

  onSubmit() {
    alert('Thanks!');
  }
}
