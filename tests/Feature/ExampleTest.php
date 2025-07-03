<?php

it('returns a redirect to dashboard', function () {
    $response = $this->get('/');

    $response->assertRedirect('/dashboard');
});
