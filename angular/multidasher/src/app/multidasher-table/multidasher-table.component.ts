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
  displayedColumns: string[] = ['name', 'wallets','balance', 'status'];
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

      // Load the status
      console.log(blockchain.id);
      const status = await this.getStatus(blockchain.id);
      console.log(status);
      blockchain.status = status['data']['status'];

      // Load the wallets
      const wallets = await this.getWallets(blockchain.id);
      blockchain.wallets = wallets;

      const balances = await this.getTotalBalance(blockchain.id);
      blockchain.balance = balances;

      this.blockchainArray.push(blockchain);
    } 
    console.log(this.blockchainArray);
    this.dataSource = new MatTableDataSource(this.blockchainArray);
    this.active = true;
  }

  async getStatus(nid : string) {
    const response = await this.dataService.getBlockchainInfo(nid).toPromise();
    return response;
  }

  async getWallets(nid : string) {
    const response = await this.dataService.getWallets(nid).toPromise();
    let wallets: Wallet[] = [];
    if(response['data']['wallet']){
    for(let data of response['data']['wallet']){
      let wallet : Wallet = {
        id: data['wallet_id'],
        name: data['name'],
        balance: data['balance'],
        address: data['address']
      }
      wallets.push(wallet);
    }
    }
    return wallets;
  }

  async getTotalBalance(nid : string) {
    const response = await this.dataService.getTotalBalance(nid).toPromise();
    let balances : Currency[] = [];
    if(response['data']['total']){
    console.log(response);
    for(let data of response['data']['total']){
      let balance : Currency = {
        name: data['name']['name'],
        balance: data['qty'],
      }
      balances.push(balance);
    }
  }
    return balances;
  }


  // async getBalance(balance) {
  //   let currencies : Currency[] = [];
  //   for (let id of balance){
  //     let currency : Currency = {
  //       'id' : id.target_id,
  //       'name' : id.target_id,
  //       'balance' : 0 
  //     }
  //     currencies.push(currency);
  //   }
  //   return currencies;
  // }

}
