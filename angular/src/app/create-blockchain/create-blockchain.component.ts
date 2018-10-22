import { Component, OnInit } from '@angular/core';
import {FormBuilder, FormGroup, Validators} from '@angular/forms';
import { DataService } from '../data-feeds.service';

@Component({
  selector: 'app-create-blockchain',
  templateUrl: './create-blockchain.component.html',
  styleUrls: ['./create-blockchain.component.scss']
})
export class CreateBlockchainComponent implements OnInit {
  isLinear = true;
  blockchainName: FormGroup;
  blockchainParams: FormGroup;

  constructor(
  	private _formBuilder: FormBuilder,
  	private dataService : DataService
  ) {}

  ngOnInit() {
    this.blockchainName = this._formBuilder.group({
      blockchainName: ['', Validators.required]
    });
    this.blockchainParams = this._formBuilder.group({
      blockchainParams: ['', Validators.required]
    });
  }

  async createBlockchain(blockchainName: String){
  	const result = await this.dataService.createBlockchain(blockchainName).toPromise();
  	let params = result['data']['params'];
  	this.blockchainParams.controls['blockchainParams'].setValue(params);
  }

  async submitBlockchain(){
  	const result = await this.dataService.submitBlockchain(this.blockchainName.value.blockchainName, this.blockchainParams.value.blockchainParams).toPromise();
  	if(result['data']['status'] == 1){
  		alert('Blockchain launched, woohoo');
  	}
  }

}
