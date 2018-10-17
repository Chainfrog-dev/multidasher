//THIS FILE CONNECTS TO THE PYTHON API 
// AND OPENS AND SAVES THE SESSION IN THE ANGULAR APPLICATION.
import { Injectable }    from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../environments/environment';

class promise {};

@Injectable() 

export class DataService {
  // Load the API based on the local environment settings
  host: string = environment.host;

  constructor(
    private http: HttpClient
  ) {
  }

  getBlockchains() : Observable<promise> {
    return this.http.get(
      this.host+'/multidasher/export/blockchain',
      {headers : new HttpHeaders(), 
      responseType: 'json'}
    );
  }

  getBlockchainInfo(nid) : Observable<promise> {
    return this.http.get(
      this.host+'/multidasher/export/'+nid+'/load-status',
      {headers : new HttpHeaders(), 
      responseType: 'json'}
    );
  }

  getAssetTransactions(nid, asset) : Observable<promise> {
    return this.http.get(
      this.host+'/multidasher/export/'+nid+'/load-transactions/'+asset,
      {headers : new HttpHeaders(), 
      responseType: 'json'}
    );
  }

  getWallets(nid) : Observable<promise> {
    return this.http.get(
      this.host+'/multidasher/export/'+nid+'/export-wallets',
      {headers : new HttpHeaders(), 
      responseType: 'json'}
    );
  }

  getRecepients(nid) : Observable<promise> {
    return this.http.get(
      this.host+'/multidasher/export/'+nid+'/export-recepients',
      {headers : new HttpHeaders(), 
      responseType: 'json'}
    );
  }

  getAssets(nid) : Observable<promise> {
    return this.http.get(
      this.host+'/multidasher/export/'+nid+'/export-assets',
      {headers : new HttpHeaders(), 
      responseType: 'json'}
    );
  }

  getTotalBalance(nid) : Observable<promise> {
    return this.http.get(
      this.host+'/multidasher/export/'+nid+'/total-balance',
      {headers : new HttpHeaders(), 
      responseType: 'json'}
    );
  }

  createBlockchain(blockchain : String) : Observable<promise> {
    return this.http.get(
      this.host+'/multidasher/launch-blockchain/'+blockchain,
      {headers : new HttpHeaders(), 
      responseType: 'json'}
    );
  }

  updateBlockchainOptions() : Observable<promise> {
    return this.http.post(
      this.host+'/multidasher/update-blockchain-options',
      '',
      {headers : new HttpHeaders(), 
      responseType: 'json'}
    );
  }

  updateAddresses(nid: String) : Observable<promise> {
    return this.http.get(
      this.host+'/multidasher/'+nid+'/update-addresses',
      {headers : new HttpHeaders(), 
      responseType: 'json'}
    );
  }

  addAddress(nid: String, title: String, permissions: String) : Observable<promise> {
    const data = {
      'title' : title,
      'permissions' : permissions
    }

    return this.http.post(
      this.host+'/multidasher/'+nid+'/add-wallet',
      data,
      {headers : new HttpHeaders(), 
      responseType: 'json'}
    );
  }

  addAsset(nid: String, title: String, assetQuantity: Number, assetOpen: Boolean, recepient: String, description: String) : Observable<promise> {
    const data = {
      'title' : title,
      'asset_name' : title,
      'asset_quantity' : assetQuantity,
      'asset_open' : assetOpen,
      'recepient' : recepient,
      'description' : description
    }
    console.log(assetQuantity);
    return this.http.post(
      this.host+'/multidasher/'+nid+'/add-asset',
      data,
      {headers : new HttpHeaders(), 
      responseType: 'json'}
    );
  }

  addRecepient(nid: String, title: String, description: String, address: String, assetName: String) : Observable<promise> {
    const data = {
      'title' : title,
      'asset_name' : assetName,
      'description' : description,
      'address' : address
    }
    console.log(data);
    return this.http.post(
      this.host+'/multidasher/'+nid+'/add-recepient',
      data,
      {headers : new HttpHeaders(), 
      responseType: 'json'}
    );
  }

  submitBlockchain(name: String,params:String) : Observable<promise> {
  	const data = {
  		'blockchain' : name,
  		'params' : params
  	}
  	console.log(data);
    return this.http.post(
      this.host+'/multidasher/submit-blockchain',
      data,
      {headers : new HttpHeaders(), 
      responseType: 'json'}
    );
  }
}

  // sessionKey$ : Observable<KeyPairStoreInterface>;
  // sessionKey : string;

    // this.sessionKey$ = store.select('keyPair');
    // this.sessionKey$.subscribe(keyPair => {
    //   this.sessionKey = keyPair['sessionKey']['session_id'];
    // })