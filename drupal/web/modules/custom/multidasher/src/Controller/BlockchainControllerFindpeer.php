<?php

namespace Drupal\multidasher\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Defines BlockchainController class.
 */
class BlockchainControllerFindpeer extends ControllerBase {

  /**
   *
   */
  public function getPeerInfo() {
    $node = $this->multidasherNodeLoad('');
    $blockchain = $node->field_blockchain_id->getString();
    $blockchain_nid = $node->id();
    $result = $this->executeRequest($blockchain, 'getpeerinfo', []);
    ksm($result);
    // $result['result'][0] = json_decode('{
    //     "id" : 144085,
    //     "addr" : "172.31.23.199:1985",
    //     "addrlocal" : "172.31.18.153:37104",
    //     "services" : "0000000000000001",
    //     "lastsend" : 1538571441,
    //     "lastrecv" : 1538571441,
    //     "bytessent" : 6741887,
    //     "bytesrecv" : 6745862,
    //     "conntime" : 1537528933,
    //     "pingtime" : 0.00694,
    //     "version" : 70002,
    //     "subver" : "/MultiChain:0.2.0.4/",
    //     "handshakelocal" : "1YCwzVAKXWQHivLUygx9QRgCx1yjmnQzhBcuJG",
    //     "handshake" : "177a32Rif1KVsjCMyQjHmNTWPaUhT8Hwo7US7u",
    //     "inbound" : false,
    //     "startingheight" : 29,
    //     "banscore" : 0,
    //     "synced_headers" : 200,
    //     "synced_blocks" : -1,
    //     "inflight" : [
    //     ],
    //     "whitelisted" : false
    // }');

    // foreach ($result as $key => $value) {

    //   $nodes = \Drupal::entityTypeManager()
    //     ->getStorage('node')
    //     ->loadByProperties(['field_peer_address' => $value->addr]);

    //   if ($node = reset($nodes)) {
    //     $node->set('field_peer_address', $value->addr);
    //     $node->set('field_peer_address_local', $value->addrlocal);
    //     $node->set('field_peer_id', $value->id);
    //     $node->field_peer_blockchain_ref = ['target_id' => $blockchain_nid];
    //   }
    //   else {

    //     $node = Node::create(['type' => 'blockchain_peer']);
    //     $node->set('title', $value->id);
    //     $node->set('field_peer_address', $value->addr);
    //     $node->set('field_peer_address_local', $value->addrlocal);
    //     $node->set('field_peer_id', $value->id);
    //     $node->field_peer_blockchain_ref = ['target_id' => $blockchain_nid];
    //     $node->status = 1;
    //     $node->enforceIsNew();

    //   }

    //   $node->save();

    // }
    return new RedirectResponse(base_path() . 'multidasher');
  }

}
