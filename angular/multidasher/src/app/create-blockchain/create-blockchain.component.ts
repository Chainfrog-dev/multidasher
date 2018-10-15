import { Component, OnInit } from '@angular/core';
import {FormBuilder, FormGroup, Validators} from '@angular/forms';
import { DataService } from '../data-feeds.service';

@Component({
  selector: 'app-create-blockchain',
  templateUrl: './create-blockchain.component.html',
  styleUrls: ['./create-blockchain.component.scss']
})
export class CreateBlockchainComponent implements OnInit {
  isLinear = false;
  firstFormGroup: FormGroup;
  secondFormGroup: FormGroup;

  constructor(
  	private _formBuilder: FormBuilder,
  	private dataService : DataService
  ) {}

  ngOnInit() {
    this.firstFormGroup = this._formBuilder.group({
      blockchainName: ['', Validators.required]
    });
    this.secondFormGroup = this._formBuilder.group({
      secondCtrl: ['', Validators.required]
    });
  }

  async createBlockchain(blockchainName: String){
  	const result = await this.dataService.createBlockchain(blockchainName).toPromise();
  	let params = result['data']['params'];
  	this.secondFormGroup.controls['secondCtrl'].setValue(params);
  	console.log(this.secondFormGroup);
  }

  async submitBlockchain(){
  	console.log(this.firstFormGroup.value.blockchainName,this.secondFormGroup.value.secondCtrl);
  	const result = await this.dataService.submitBlockchain(this.firstFormGroup.value.blockchainName, this.secondFormGroup.value.secondCtrl).toPromise();
  	console.log(result);
  	if(result['data']['status'] == 1){
  		console.log('alertibng');
  		alert(await this.dataService.updateBlockchainOptions().toPromise());
  	}
  }

}
