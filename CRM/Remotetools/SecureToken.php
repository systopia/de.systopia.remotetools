<?php
/*-------------------------------------------------------+
| SYSTOPIA Remote Tools                                  |
| Copyright (C) 2020 SYSTOPIA                            |
| Author: B. Endres (endres@systopia.de)                 |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*/

declare(strict_types = 1);

use CRM_Remotetools_ExtensionUtil as E;
use Webmozart\Assert\Assert;

/**
 * SecureToken function to be used in links
 *  Note that the payload is not encrypted, just signed with the hash key
 */
class CRM_Remotetools_SecureToken {

  private const SIGNATURE_LENGTH = 32;

  /**
   * Generic token generation with payload
   *
   * @phpstan-param mixed $payload
   *   any serializable data
   *
   * @param string $hashKey to be used
   *
   * @return string
   *   token (URL proof)
   */
  public static function generateToken(mixed $payload, string $hashKey): string {
    $payloadJson = json_encode($payload);
    Assert::string($payloadJson);
    $encodedRawPayload = base64_encode($payloadJson);
    $signature = substr(hash('sha512', $encodedRawPayload . $hashKey), 0, self::SIGNATURE_LENGTH);
    return "{$encodedRawPayload}-{$signature}";
  }

  /**
   * Generate a token for a given entity ID
   *  The payload generated will be
   *   [
   *       0 first character of entity name,
   *       1 entity_id,
   *       2 expires date or '0',
   *       3 usage indicator,
   *       4 salt,
   *   ]
   *
   * @param string $entity_name
   *   CiviCRM entity (must be supported)
   *
   * @param integer $entity_id
   *   CiviCRM ID
   *
   * @param string $expires
   *   strtotime()-readable timestamp
   *
   * @param string $usage
   *   what should this token be used for
   *
   * @return string
   *   hash token
   *
   * @throws \Exception
   *   If the related contact does not exist, is deleted, or has no proper 32 character hash.
   */
  public static function generateEntityToken($entity_name, $entity_id, $expires = NULL, $usage = NULL) {
    // build the payload
    if (empty($expires)) {
      $expires = 0;
    }
    else {
      $expires = strtotime($expires);
    }
    if (empty($usage)) {
      $usage = '';
    }

    // add salt
    $salt = base64_encode(random_bytes(8));

    $payload = [strtoupper(substr($entity_name, 0, 2)), $entity_id, $expires, $usage, $salt];

    // get the contact hash
    $hash = self::getContactHash($entity_name, $entity_id);

    if (empty($hash) || strlen($hash) < 32) {
      throw new Exception(E::ts("Couldn't generate token, related contact might be deleted or has no proper hash"));
    }

    // generate the token
    return self::generateToken($payload, $hash);
  }

  /**
   * Decode and verify an entity token as generated with the ::generateEntityToken function
   *
   * @param string $entity_name
   *   name of the entity
   *
   * @param string $token
   *   the token received
   *
   * @param string $usage
   *   what should this token be used for
   *
   * @return null|integer
   *   return the entity ID if the token is valid and has not expired
   */
  public static function decodeEntityToken($entity_name, $token, $usage = NULL) {
    [$encoded_raw_payload, $signature] = explode('-', $token, 2);
    $payload = json_decode(base64_decode($encoded_raw_payload), TRUE);

    // verify payload
    if (!is_array($payload) || count($payload) != 5) {
      // this is not what we're expecting
      return NULL;
    }

    // verify entity
    if (strtoupper(substr($entity_name, 0, 2)) != $payload[0]) {
      // we were expecting the initial of the entity, this seems like a mismatch
      return NULL;
    }

    // verify usage
    if (empty($usage)) {
      $usage = '';
    }
    if ($usage != $payload[3]) {
      return NULL;
    }

    // verify timeout
    $expiration = (int) $payload[2];
    if ($expiration > 0) {
      // check timeout
      if (strtotime('now') > $expiration) {
        // token expired
        return NULL;
      }
    }

    // finally: verify signature
    $entity_id = (int) $payload[1];
    $hash_key = self::getContactHash($entity_name, $entity_id);
    if (self::verifySignature($token, $hash_key)) {
      return $entity_id;
    }
    else {
      // signature not valid
      return NULL;
    }
  }

  /**
   * Generic token verification
   *
   * @param string $token
   *   the token received
   *
   * @param string $hash_key that was expected to be used
   *
   * @return boolean
   *   is the token valid?
   */
  public static function verifySignature($token, $hash_key) {
    [$encoded_raw_payload, $signature] = explode('-', $token, 2);
    $expected_signature = substr(hash('sha512', $encoded_raw_payload . $hash_key), 0, self::SIGNATURE_LENGTH);
    return $signature == $expected_signature;
  }

  /**
   * Get the contact hash for any entity linked to a contact
   *
   * @param string $entity_name
   *  name of the entity (as used by the API)
   *
   * @param integer $entity_id
   *  ID of the entity
   */
  protected static function getContactHash($entity_name, $entity_id) {
    // first, get the contact ID
    if (strtolower($entity_name) == 'contact') {
      $contact_id = (int) $entity_id;
    }
    else {
      // todo: add exeptions (like activity)?
      $contact_id = (int) civicrm_api3($entity_name, 'getvalue', ['id' => (int) $entity_id, 'return' => 'contact_id']);
    }

    // now that we have the contact ID, we can get the hash
    return CRM_Core_DAO::singleValueQuery("
            SELECT hash
            FROM civicrm_contact
            WHERE id = {$contact_id}
            AND (is_deleted = NULL OR is_deleted = 0)");
  }

}
