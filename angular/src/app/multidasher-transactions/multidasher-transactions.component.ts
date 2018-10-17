import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router, NavigationEnd } from '@angular/router';
import { DataService } from '../data-feeds.service';

@Component({
  selector: 'app-multidasher-transactions',
  templateUrl: './multidasher-transactions.component.html',
  styleUrls: ['./multidasher-transactions.component.scss']
})
export class MultidasherTransactionsComponent implements OnInit {
  blockchainId : String;
  assetId: String;

  constructor(
  	private route: ActivatedRoute,
  	private dataService : DataService
  	) {}

  ngOnInit() {
  	this.blockchainId = this.route.snapshot.params['blockchainId'];
  	this.assetId = this.route.snapshot.params['asset'];
  	this.getAssetTransactions();
  }

  async getAssetTransactions() {
  	const response = await this.dataService.getAssetTransactions(this.blockchainId,this.assetId).toPromise();
  	console.log(response);
  }

}
