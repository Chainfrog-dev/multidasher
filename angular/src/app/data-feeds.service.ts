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
      this.host+'/multidasher/export/export-blockchains',
      {headers : new HttpHeaders(), 
      responseType: 'json'}
    );
  }

  getBlockchainInfo(nid) : Observable<promise> {
    return this.http.get(
      this.host+'/multidasher/export/'+nid+'/status',
      {headers : new HttpHeaders(), 
      responseType: 'json'}
    );
  }

  getBlockchainMaster(blockchain, stream) : Observable<promise> {
    const data = {
      'blockchain' : blockchain,
      'stream' : stream
    }

    return this.http.post(
      this.host+'/multidasher/access/retrieve-master/',
      data,
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

  deleteBlockchain(nid) : Observable<promise> {
    const data = {
      'nid' : nid,
    }

    return this.http.post(
      this.host+'/multidasher/cron/delete',
      data,
      {headers : new HttpHeaders(), 
      responseType: 'json'} 
    ); 
  }

  getMasterJson(blockchain, stream, author) : Observable<promise> {
    const data = {
      'blockchain' : blockchain,
      'stream' : stream,
      'author' : author
    } 

    return this.http.post(
      this.host+'/multidasher/access/retrieve-master-json',
      data,
      {headers : new HttpHeaders(), 
      responseType: 'json'}
    );
  }

  initiateRemoteBlockchain(blockchain, ip, port) : Observable<promise> {
    console.log(ip);
    const data = {
      'blockchain' : blockchain,
      'chainAddress' : ip,
      'port' : port
    } 

    return this.http.post(
      this.host+'/multidasher/access/initiate-remote',
      data,
      {headers : new HttpHeaders(), 
      responseType: 'json'}
    );
  }

  getWallets(nid) : Observable<promise> {
    return this.http.get(
      this.host+'/multidasher/export/'+nid+'/wallets',
      {headers : new HttpHeaders(), 
      responseType: 'json'}
    );
  }

  getRecepients(nid) : Observable<promise> {
    return this.http.get(
      this.host+'/multidasher/export/'+nid+'/recepients',
      {headers : new HttpHeaders(), 
      responseType: 'json'}
    );
  }

  getAssets(nid) : Observable<promise> {
    return this.http.get(
      this.host+'/multidasher/export/'+nid+'/assets',
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
      this.host+'/multidasher/create/launch-blockchain/'+blockchain,
      {headers : new HttpHeaders(), 
      responseType: 'json'}
    );
  }

  registerBlockchain(url : string, chainAddress: String, data: any) : Observable<promise> {
    console.log(url); console.log(chainAddress); console.log(data);
    return this.http.post(
      url,
      data,
      {headers : new HttpHeaders()} 
    );
  }

  updateBlockchains() : Observable<promise> {
    return this.http.post(
      this.host+'/multidasher/cron/update-blockchains',
      '',
      {headers : new HttpHeaders(), 
      responseType: 'json'}
    );
  }

  bootstrapBlockchain(blockchain : String) : Observable<promise> {
    return this.http.post(
      this.host+'/multidasher/cron/bootstrap-blockchain/'+blockchain,
      '',
      {headers : new HttpHeaders(), 
      responseType: 'json'}
    );
  }

  updateAddresses(nid: String) : Observable<promise> {
    return this.http.get(
      this.host+'/multidasher/cron/'+nid+'/update-address',
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
      this.host+'/multidasher/add/'+nid+'/wallet',
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
      this.host+'/multidasher/add/'+nid+'/asset',
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
    return this.http.post(
      this.host+'/multidasher/add/'+nid+'/recepient',
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
      this.host+'/multidasher/create/blockchain-params',
      data,
      {headers : new HttpHeaders(), 
      responseType: 'json'}
    );
  }

  writeStream(blockchain: String, stream: String, key:String, message: any) : Observable<promise> {
    const data = {
      'stream' : stream,
      'key' : key,
      'message' : message,
      'blockchain' : blockchain
    }
    console.log(data);
    return this.http.post(
      this.host+'/multidasher/stream/publish',
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
