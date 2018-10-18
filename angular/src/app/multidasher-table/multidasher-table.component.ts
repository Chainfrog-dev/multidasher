import { Component, OnInit, ViewChild } from '@angular/core';
import { MatPaginator, MatSort, MatTableDataSource } from '@angular/material';
import { DataService } from '../data-feeds.service';

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

export interface Blockchain {
  name: string;
  id: string;
  description?: string;
  status: string;
  wallets? : Wallet[];
  balance? : Currency[];
}

@Component({
  selector: 'app-multidasher-table',
  templateUrl: './multidasher-table.component.html',
  styleUrls: ['./multidasher-table.component.scss']
})

export class MultidasherTableComponent implements OnInit {
  /** Columns displayed in the table. Columns IDs can be added, removed, or reordered. */
  displayedColumns: string[] = ['name', 'wallets','balance', 'status', 'refresh', 'info'];
  dataSource = new MatTableDataSource();
  blockchainArray : Blockchain[] = [];
  active: boolean = false;

  constructor(
    private dataService : DataService
  ) {}

  @ViewChild(MatPaginator) paginator: MatPaginator;
  @ViewChild(MatSort) sort: MatSort;

  ngOnInit() {
    this.dataSource.sort = this.sort;
    this.dataSource = new MatTableDataSource(this.blockchainArray);
    this.getBlockchains();
  }

  async getBlockchains() {
    const result = await this.dataService.getBlockchains().toPromise();
    for(let value of result['data']){
      let blockchain : Blockchain = 
        {
          'name' : value.name,
          'id' : value.id,
          'description' : value.description,
          'status' : '',
          'wallets' : [],
          'balance' : []
        }
      this.blockchainArray.push(blockchain);
      this.refreshBlockchain(blockchain);
    } 
    this.active = true;
  }

  async getStatus(nid : string) {
    const response = await this.dataService.getBlockchainInfo(nid).toPromise();
    return response;
  }

  async getWallets(nid : string) {
    const response = await this.dataService.getWallets(nid).toPromise();
    let wallets: Wallet[] = [];
    if(response['data']){
      for(let key in response['data']){
        let wallet : Wallet = {
          id: response['data'][key]['wallet_id'],
          name: response['data'][key]['name'],
          balance: response['data'][key]['balance'],
          address: response['data'][key]['address']
        }
        wallets.push(wallet);
      }
    }
    console.log(wallets);
    return wallets;
  }

  async getTotalBalance(nid : string) {
    const response = await this.dataService.getTotalBalance(nid).toPromise();
    let balances : Currency[] = [];
    if(response['data']['total']){
      for(let data of response['data']['total']){
        let balance : Currency = {
          name: data['name'],
          balance: data['qty'],
        }
        balances.push(balance);
      }
    }
    return balances;
  }

  async refreshBlockchain(blockchain: Blockchain) {
      // Load the status
      const status = await this.getStatus(blockchain.id);
      blockchain.status = status['data']['status'];

      // Load the wallets
      const wallets = await this.getWallets(blockchain.id);
      blockchain.wallets = wallets;

      const balances = await this.getTotalBalance(blockchain.id);
      blockchain.balance = balances;

      let tableItem = this.blockchainArray.filter(item => item['id'].indexOf(blockchain.id) === 0)[0];
      tableItem = blockchain;
  }

}
