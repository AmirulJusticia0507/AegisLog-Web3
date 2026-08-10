// SPDX-License-Identifier: MIT
pragma solidity ^0.8.24;

/// @title AuditVault
/// @notice Anchors SHA-256 hashes of audit reports on-chain to guarantee
///         integrity against off-chain tampering. Zero PII is stored: only
///         the audit UUID and the cryptographic hash touch the ledger.
contract AuditVault {
    struct Anchor {
        bytes32 fileHash;
        address anchoredBy;
        uint256 blockNumber;
        uint256 anchoredAt;
    }

    mapping(bytes32 auditId => Anchor) private anchors;

    event LogAnchored(
        bytes32 indexed auditId,
        bytes32 fileHash,
        address indexed anchoredBy,
        uint256 indexed blockNumber,
        uint256 anchoredAt
    );

    error AuditAlreadyAnchored(bytes32 auditId);
    error AuditNotFound(bytes32 auditId);
    error HashMismatch(bytes32 auditId, bytes32 expectedHash, bytes32 actualHash);

    /// @notice Anchor a SHA-256 hash under a unique audit UUID.
    /// @dev Reverts if the auditId has already been anchored to prevent
    ///      silent overwrites (tamper resistance).
    function anchor(bytes32 auditId, bytes32 fileHash) external {
        if (anchors[auditId].anchoredAt != 0) {
            revert AuditAlreadyAnchored(auditId);
        }

        anchors[auditId] = Anchor({
            fileHash: fileHash,
            anchoredBy: msg.sender,
            blockNumber: block.number,
            anchoredAt: block.timestamp
        });

        emit LogAnchored(auditId, fileHash, msg.sender, block.number, block.timestamp);
    }

    /// @notice Verify that the stored on-chain hash still matches an expected hash.
    function verify(bytes32 auditId, bytes32 expectedHash) external view returns (bool) {
        Anchor memory anchor = anchors[auditId];
        if (anchor.anchoredAt == 0) {
            revert AuditNotFound(auditId);
        }

        return anchor.fileHash == expectedHash;
    }

    /// @notice Returns the full on-chain anchor record.
    function getAnchor(
        bytes32 auditId
    ) external view returns (bytes32 fileHash, address anchoredBy, uint256 blockNumber, uint256 anchoredAt) {
        Anchor memory anchor = anchors[auditId];
        if (anchor.anchoredAt == 0) {
            revert AuditNotFound(auditId);
        }

        return (anchor.fileHash, anchor.anchoredBy, anchor.blockNumber, anchor.anchoredAt);
    }

    function isAnchored(bytes32 auditId) external view returns (bool) {
        return anchors[auditId].anchoredAt != 0;
    }
}
