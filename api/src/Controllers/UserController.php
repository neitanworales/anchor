<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Repository/UserRepository.php';
require_once __DIR__ . '/../Repository/UserRoleRepository.php';
require_once __DIR__ . '/../Repository/EventRegistrationRepository.php';
require_once __DIR__ . '/../Services/AuthService.php';

class UserController extends BaseController
{
    private $users;
    private $authService;
    private $userRoles;
    private $eventRegistrations;

    public function __construct()
    {
        $this->users = new UserRepository();
        $this->authService = new AuthService();
        $this->userRoles = new UserRoleRepository();
        //$this->eventRegistrations = new EventRegistrationRepository();
    }

    public function detail($request)
    {
        $userId = isset($request['user_id']) ? (int) $request['user_id'] : 0;
        if ($userId <= 0) {
            return $this->fail('user_id is required', 422);
        }

        $user = $this->users->findModelById($userId);
        if (!$user) {
            return $this->fail('user not found', 404);
        }

        return $this->ok(array('user' => $user->toArray()), 'user found');
    }

    public function profile($request)
    {
        if (isset($request['auth_user']) && $request['auth_user']) {
            $userId = (int) $request['auth_user']->id;
            $roles = $this->userRoles->getRolesByUserId($userId);
            return $this->ok(array(
                'user' => $request['auth_user']->toArray(),
                'roles' => $roles
            ), 'profile found');
        }

        $token = isset($request['token']) ? trim($request['token']) : '';
        if ($token === '') {
            return $this->fail('token is required', 422);
        }

        $user = $this->authService->validateToken($token);
        if (!$user) {
            return $this->fail('invalid or expired token', 401);
        }

        $userId = (int) $user->id;
        $roles = $this->userRoles->getRolesByUserId($userId);
        return $this->ok(array(
            'user' => $user->toArray(),
            'roles' => $roles
        ), 'profile found');
    }

    public function updateRoles($request)
    {
        $targetUserId = isset($request['user_id']) ? (int) $request['user_id'] : 0;
        if ($targetUserId <= 0) {
            return $this->fail('user_id is required', 422);
        }

        $targetUser = $this->users->findModelById($targetUserId);
        if (!$targetUser) {
            return $this->fail('user not found', 404);
        }

        $hasSyncPayload = isset($request['roles']);
        $requestedRoles = $this->extractRolesFromRequest($request, 'roles');
        $addRoles = $this->extractRolesFromRequest($request, 'add_roles');
        $removeRoles = $this->extractRolesFromRequest($request, 'remove_roles');

        if ($hasSyncPayload) {
            $currentRoles = $this->userRoles->getRolesByUserId($targetUserId);
            $toAdd = array_values(array_diff($requestedRoles, $currentRoles));
            $toRemove = array_values(array_diff($currentRoles, $requestedRoles));

            $added = $this->userRoles->addRoles($targetUserId, $toAdd);
            $removed = $this->userRoles->removeRoles($targetUserId, $toRemove);
        } else {
            if (empty($addRoles) && empty($removeRoles)) {
                return $this->fail('provide roles, add_roles, or remove_roles', 422);
            }

            $overlap = array_intersect($addRoles, $removeRoles);
            if (!empty($overlap)) {
                return $this->fail('the same role cannot be both added and removed', 422, array('roles' => array_values($overlap)));
            }

            $added = $this->userRoles->addRoles($targetUserId, $addRoles);
            $removed = $this->userRoles->removeRoles($targetUserId, $removeRoles);
        }

        $finalRoles = $this->userRoles->getRolesByUserId($targetUserId);
        sort($finalRoles);

        return $this->ok(array(
            'user_id' => $targetUserId,
            'roles' => $finalRoles,
            'added' => $added,
            'removed' => $removed,
        ), 'roles updated');
    }

    public function updateEventRoles($request)
    {
        $targetUserId = isset($request['user_id']) ? (int) $request['user_id'] : 0;
        $eventId = isset($request['event_id']) ? (int) $request['event_id'] : 0;
        if ($targetUserId <= 0 || $eventId <= 0) {
            return $this->fail('user_id and event_id are required', 422);
        }

        $targetUser = $this->users->findModelById($targetUserId);
        if (!$targetUser) {
            return $this->fail('user not found', 404);
        }

        $registration = $this->eventRegistrations->findByEventAndUser($eventId, $targetUserId);
        if (!$registration) {
            return $this->fail('event registration not found for user', 404);
        }

        $hasEventRolesPayload = isset($request['event_roles']);
        $eventRoles = $this->extractRolesFromRequest($request, 'event_roles');
        $hasEventStaffPayload = array_key_exists('event_is_staff', $request);
        $hasEventAdminPayload = array_key_exists('event_is_admin', $request);
        $hasEventPayload = $hasEventRolesPayload || $hasEventStaffPayload || $hasEventAdminPayload;

        if (!$hasEventPayload) {
            return $this->fail('provide event_roles, event_is_staff, or event_is_admin', 422);
        }

        $isStaff = ((int) $registration->is_staff === 1) ? 1 : 0;
        $isAdmin = ((int) $registration->is_admin === 1) ? 1 : 0;

        if ($hasEventRolesPayload) {
            $isStaff = in_array('staff', $eventRoles, true) ? 1 : 0;
            $isAdmin = in_array('admin', $eventRoles, true) ? 1 : 0;
        }

        if ($hasEventStaffPayload) {
            $parsedStaff = $this->parseBoolean($request['event_is_staff']);
            if ($parsedStaff === null) {
                return $this->fail('event_is_staff must be boolean-like (true/false/1/0)', 422);
            }
            $isStaff = $parsedStaff ? 1 : 0;
        }

        if ($hasEventAdminPayload) {
            $parsedAdmin = $this->parseBoolean($request['event_is_admin']);
            if ($parsedAdmin === null) {
                return $this->fail('event_is_admin must be boolean-like (true/false/1/0)', 422);
            }
            $isAdmin = $parsedAdmin ? 1 : 0;
        }

        $affectedEventRows = $this->eventRegistrations->updateRolesByUserAndEvent($targetUserId, $eventId, $isStaff, $isAdmin);

        // Event-level changes must propagate to global role assignments.
        $shouldHaveStaffRole = $this->eventRegistrations->userHasAnyStaffRegistration($targetUserId);
        $shouldHaveAdminRole = $this->eventRegistrations->userHasAnyAdminRegistration($targetUserId);

        if ($shouldHaveStaffRole) {
            $this->userRoles->addRoles($targetUserId, array('staff'));
        } else {
            $this->userRoles->removeRoles($targetUserId, array('staff'));
        }

        if ($shouldHaveAdminRole) {
            $this->userRoles->addRoles($targetUserId, array('admin'));
        } else {
            $this->userRoles->removeRoles($targetUserId, array('admin'));
        }

        $finalRoles = $this->userRoles->getRolesByUserId($targetUserId);
        sort($finalRoles);

        return $this->ok(array(
            'user_id' => $targetUserId,
            'event_id' => $eventId,
            'event_registration' => array(
                'is_staff' => $isStaff,
                'is_admin' => $isAdmin,
                'affected' => $affectedEventRows,
            ),
            'roles' => $finalRoles,
        ), 'event roles updated');
    }

    private function parseBoolean($value)
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            if ((int) $value === 1) {
                return true;
            }
            if ((int) $value === 0) {
                return false;
            }
            return null;
        }

        $normalized = strtolower(trim((string) $value));
        if (in_array($normalized, array('1', 'true', 'yes', 'on'), true)) {
            return true;
        }

        if (in_array($normalized, array('0', 'false', 'no', 'off'), true)) {
            return false;
        }

        return null;
    }

    private function extractRolesFromRequest($request, $key)
    {
        if (!isset($request[$key])) {
            return array();
        }

        $value = $request[$key];
        if (!is_array($value)) {
            $value = explode(',', (string) $value);
        }

        $roles = array();
        foreach ($value as $role) {
            $normalized = strtolower(trim((string) $role));
            if ($normalized === '') {
                continue;
            }

            if (!preg_match('/^[a-z0-9_-]+$/', $normalized)) {
                continue;
            }

            $roles[] = $normalized;
        }

        return array_values(array_unique($roles));
    }
}
