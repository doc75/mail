<?php

declare(strict_types=1);

/**
 * Mail App
 *
 * @copyright 2022 Anna Larch <anna.larch@gmx.net>
 *
 * @author Anna Larch <anna.larch@gmx.net>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Mail\Controller;

use OCA\Mail\Db\LocalMailboxMessage;
use OCA\Mail\Exception\ClientException;
use OCA\Mail\Exception\ServiceException;
use OCA\Mail\Service\AccountService;
use OCA\Mail\Service\OutboxService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;

class OutboxController extends Controller {

	/** @var OutboxService */
	private $service;

	/** @var string */
	private $userId;

	/** @var AccountService */
	private $accountService;

	public function __construct(string $appName,
								$UserId,
								IRequest $request,
								OutboxService $service,
	AccountService $accountService) {
		parent::__construct($appName, $request);
		$this->userId = $UserId;
		$this->service = $service;
		$this->accountService = $accountService;
	}

	/**
	 * @NoAdminRequired
	 *
	 * @return JSONResponse
	 */
	public function index(): JSONResponse {
		try {
			return new JSONResponse(
				[
					'messages' => $this->service->getMessages($this->userId)
				]
			);
		} catch (ServiceException $e) {
			return new JSONResponse($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * @NoAdminRequired
	 *
	 * @param int $id
	 * @return JSONResponse
	 */
	public function get(int $id): JSONResponse {
		try {
			$message = $this->service->getMessage($id);
			$this->accountService->find($this->userId, $message->getAccountId());
		} catch (ServiceException | ClientException $e) {
			return new JSONResponse($e->getMessage(), $e->getCode());
		}
		return new JSONResponse(
			$message
		);
	}

	/**
	 * @NoAdminRequired
	 *
	 * @param int $accountId
	 * @param int $sendAt
	 * @param string $subject
	 * @param string $body
	 * @param bool $isHtml
	 * @param bool $isMdn
	 * @param string $inReplyToMessageId
	 * @param array $recipients
	 * @param array $attachmentIds
	 * @return JSONResponse
	 */
	public function save(
		int    $accountId,
		int    $sendAt,
		string $subject,
		string $body,
		bool   $isHtml,
		bool   $isMdn,
		string $inReplyToMessageId,
		array  $recipients,
		array  $attachmentIds
	): JSONResponse {
		try {
			$this->accountService->find($this->userId, $accountId);
		} catch (ClientException $e) {
			return new JSONResponse($e->getMessage(), $e->getCode());
		}

		$message = new LocalMailboxMessage();
		$message->setAccountId($accountId);
		$message->setSendAt($sendAt);
		$message->setSubject($subject);
		$message->setBody($body);
		$message->setHtml($isHtml);
		$message->setMdn($isMdn);
		$message->setInReplyToMessageId($inReplyToMessageId);

		try {
			$this->service->saveMessage($message, $recipients, $attachmentIds);
		} catch (ServiceException $e) {
			return new JSONResponse($e->getMessage(), $e->getCode());
		}

		// Return with related here?
		return new JSONResponse(
			$message, Http::STATUS_CREATED
		);
	}

	/**
	 * @NoAdminRequired
	 *
	 * @param int $id
	 * @return JSONResponse
	 */
	public function send(int $id):JSONResponse {
		try {
			$message = $this->service->getMessage($id);
			$account = $this->accountService->find($this->userId, $message->getAccountId());
			$this->service->sendMessage($message, $account);
		} catch (ServiceException | ClientException $e) {
			return new JSONResponse($e->getMessage(), $e->getCode());
		}
		return new JSONResponse(
			'Message sent', Http::STATUS_ACCEPTED
		);
	}

	/**
	 * @NoAdminRequired
	 *
	 * @param int $id
	 * @return JSONResponse
	 */
	public function delete(int $id): JSONResponse {
		try {
			$message = $this->service->getMessage($id);
			$this->accountService->find($this->userId, $message->getAccountId());
			$this->service->deleteMessage($message, $this->userId);
		} catch (ServiceException | ClientException $e) {
			return new JSONResponse($e->getMessage(), $e->getCode());
		}
		return new JSONResponse('Message deleted', Http::STATUS_ACCEPTED);
	}
}
