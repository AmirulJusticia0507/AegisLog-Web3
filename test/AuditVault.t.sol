// SPDX-License-Identifier: MIT
pragma solidity ^0.8.24;

import {Test, console} from "forge-std/Test.sol";
import {AuditVault} from "../contracts/AuditVault.sol";

contract AuditVaultTest is Test {
    AuditVault private vault;

    bytes32 private constant AUDIT_ID = keccak256("audit-001");
    bytes32 private constant HASH =
        bytes32(0xe3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855);

    function setUp() public {
        vault = new AuditVault();
    }

    function testAnchorAndVerify() public {
        vault.anchor(AUDIT_ID, HASH);

        assertTrue(vault.isAnchored(AUDIT_ID));
        assertTrue(vault.verify(AUDIT_ID, HASH));
    }

    function testVerifyFailsOnMismatch() public {
        vault.anchor(AUDIT_ID, HASH);

        assertFalse(vault.verify(AUDIT_ID, keccak256("tampered-hash")));
    }

    function testRevertsOnDoubleAnchor() public {
        vault.anchor(AUDIT_ID, HASH);

        vm.expectRevert(abi.encodeWithSelector(AuditVault.AuditAlreadyAnchored.selector, AUDIT_ID));
        vault.anchor(AUDIT_ID, HASH);
    }

    function testRevertsWhenNotFound() public {
        vm.expectRevert(abi.encodeWithSelector(AuditVault.AuditNotFound.selector, AUDIT_ID));
        vault.getAnchor(AUDIT_ID);
    }
}
