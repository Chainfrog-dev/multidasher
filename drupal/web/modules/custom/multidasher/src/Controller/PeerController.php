<?php

namespace Drupal\multidasher\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Defines PeerController class.
 */
class PeerController extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    $this->blockchainController = new RequestsController();
  }

  /**
   * Loads peer information from Multichain.
   */
  public function getPeerInfo() {
    $node = $this->blockchainController->multidasherNodeLoad('');
    $blockchain = $node->field_blockchain_id->getString();
    $blockchain_nid = $node->id();
    $result = $this->executeRequest($blockchain, 'getpeerinfo', []);
    foreach ($result as $key => $value) {
      $nodes = \Drupal::entityTypeManager()
        ->getStorage('node')
        ->loadByProperties(['field_peer_address' => $value->addr]);
      if ($node = reset($nodes)) {
        $node->set('field_peer_address', $value->addr);
        $node->set('field_peer_address_local', $value->addrlocal);
        $node->set('field_peer_id', $value->id);
        $node->field_peer_blockchain_ref = ['target_id' => $blockchain_nid];
      }
      else {
        $node = Node::create(['type' => 'blockchain_peer']);
        $node->set('title', $value->id);
        $node->set('field_peer_address', $value->addr);
        $node->set('field_peer_address_local', $value->addrlocal);
        $node->set('field_peer_id', $value->id);
        $node->field_peer_blockchain_ref = ['target_id' => $blockchain_nid];
        $node->status = 1;
        $node->enforceIsNew();
      }
      $node->save();
    }
    return new RedirectResponse(base_path() . 'multidasher');
  }

}
