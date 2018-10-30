import { Component, OnInit } from '@angular/core';
import { FormBuilder, Validators } from '@angular/forms';
import { DataService } from '../data-feeds.service';
import { ActivatedRoute, Router, NavigationEnd } from '@angular/router';    

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
  selector: 'app-send-asset',
  templateUrl: './send-asset.component.html',
  styleUrls: ['./send-asset.component.scss']
})
export class SendAssetComponent implements OnInit {
  wallets: Wallet[] = [];
  blockchainId : String;
  assetForm = this.fb.group({
    title: [null, Validators.required],
    assetQuantity: [null, Validators.required],
    assetOpen: [null, Validators.required],
    recipient: [null, Validators.required],
    description: [null, Validators.required]
  });

  constructor(
    private fb: FormBuilder,
    private route: ActivatedRoute,
    private dataService : DataService
  ) { }

  ngOnInit() {
    this.blockchainId = this.route.snapshot.params['blockchainId'];
    this.getWallets(this.blockchainId);
  }

  async submitForm() {
    const result = await this.dataService.addAsset(this.blockchainId,this.assetForm.value.title,this.assetForm.value.assetQuantity,this.assetForm.value.assetOpen,this.assetForm.value.recipient, this.assetForm.value.description).toPromise();
    console.log(result);
    if(result['status'] == 1){
      alert('congrats, '+result['data']['message']['result']);
      var message = await this.dataService.updateAddresses(this.blockchainId).toPromise();
      alert(message['message']);
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

}
