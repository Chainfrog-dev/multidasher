import { Component, OnInit } from '@angular/core';
import { FormBuilder, Validators } from '@angular/forms';
import { DataService } from '../data-feeds.service';
import { ActivatedRoute, Router, NavigationEnd } from '@angular/router';    

export interface Asset {
	description: string;
	name: string;
}

export interface Currency {
	id?: string;
	name: string;
	balance: string;
}
export interface Wallet {
	id: string;
	name: string;
	balance: Currency[];
	address: string;
}


@Component({
  selector: 'app-create-recipient',
  templateUrl: './create-recipient.component.html',
  styleUrls: ['./create-recipient.component.scss']
})
export class CreateRecipientComponent implements OnInit {
  blockchainId : String;
  wallets: Wallet[] = [];
	assets: Asset[] = [];

  recipientForm = this.fb.group({
    'title': [null, Validators.required],
    'description': [null, Validators.required],
    'wallet': [null, Validators.required],
    'assetName': [null, Validators.required]
  });

  constructor(
    private fb: FormBuilder,
    private route: ActivatedRoute,
    private dataService : DataService
  ) {}

  ngOnInit() {
  	this.blockchainId = this.route.snapshot.params['blockchainId'];
  	this.getWallets(this.blockchainId);
  	this.getAssets(this.blockchainId);
  }

  async submitForm() {
    const result = await this.dataService.addRecipient(this.blockchainId,this.recipientForm.value.title, this.recipientForm.value.description, this.recipientForm.value.wallet, this.recipientForm.value.assetName).toPromise();
    console.log(result);
    if(result['status'] == 1){
      alert('congrats, recipient added');
    }
  }

  	async getWallets(nid : String) {
		const response = await this.dataService.getWallets(nid).toPromise();
		for(let key in response['data']){
			let wallet : Wallet = {
				id: response['data'][key]['wallet_id'],
				name: response['data'][key]['name'],
				balance: response['data'][key]['balance'],
				address: response['data'][key]['address']
			}
	        if (this.wallets.filter(item=> item.id == wallet.id).length == 0){
				this.wallets.push(wallet);
	        }
		}
	}

	async getAssets(nid : String) {
		const response = await this.dataService.getAssets(nid).toPromise();
		console.log(response);
		for(let key in response['data']){
			let asset : Asset = {
				'description': response['data'][key]['description'],
				'name': response['data'][key]['name'],
			}
      if (this.assets.filter(item=> item.name == asset.name).length == 0){
				this.assets.push(asset);
      }
		}

	}

}
